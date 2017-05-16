<?php

namespace Level23\AwsQueue\Queue\Jobs;

use Illuminate\Support\Collection;

class BatchJob extends SqsJob
{
    /**
     * @var SqsJob[]
     */
    protected $jobs = [];

    /**
     * @param SqsJob $job
     */
    public function pushJob(SqsJob $job)
    {
        $this->jobs[$job->getJobId()] = $job;
    }

    /**
     * @return SqsJob[]
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        return 1;
    }

    /**
     * Delete the job from the queue.
     *
     * @return Collection
     */
    public function delete()
    {
        $jobs = collect($this->jobs)->filter(function (SqsJob $job) {
            return !$job->isDeletedOrReleased();
        });

        $response = $this->deleteJobs($jobs);

        $this->deleted = true;

        return $response;
    }

    /**
     * @param Collection $jobs
     * @return Collection
     */
    public function deleteJobs(Collection $jobs)
    {
        $jobs = $jobs->keyBy(function(SqsJob $job) {
            return $job->getJobId();
        });

        $entries = $jobs->map(function (SqsJob $job) {
            return [
                'Id'            => $job->getJobId(),
                'ReceiptHandle' => $job->getReceiptHandle(),
            ];
        });

        $failedJobs = collect();

        if ($entries->isNotEmpty()) {

            $response = $this->sqs->deleteMessageBatch([
                'QueueUrl' => $this->queue,
                'Entries'  => $entries->values()->toArray(),
            ]);

            foreach (collect($response->get('Successful')) as $message) {
                $this->jobs[$message['Id']]->setDeleted();
            }

            foreach (collect($response->get('Failed')) as $message) {
                $job = $jobs->get($message['Id']);
                $job->setError(array_except($message,'Id'));
                $failedJobs->put($message['Id'],$job);
            }
        }

        return $failedJobs;
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int $delay
     * @return void
     */
    public function release($delay = 0)
    {
        $entries = collect($this->jobs)->transform(function (SqsJob $job) use ($delay) {
            return [
                'Id'                => $job->getJobId(),
                'ReceiptHandle'     => $job->getReceiptHandle(),
                'VisibilityTimeout' => $delay,
            ];
        });

        $response = $this->sqs->changeMessageVisibilityBatch([
            'QueueUrl' => $this->queue,
            'Entries'  => $entries->values()->toArray(),
        ]);

        foreach (collect($response->get('Successful')) as $message) {
            $this->jobs[$message['Id']]->setDeleted();
        }
    }
}
