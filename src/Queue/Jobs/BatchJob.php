<?php

namespace Level23\AwsQueue\Queue\Jobs;

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
     * @return void
     */
    public function delete()
    {
        $entries = collect($this->jobs)->transform(function (SqsJob $job) {
            return [
                'Id' => $job->getJobId(),
                'ReceiptHandle' => $job->getReceiptHandle()
            ];
        });

        $response = $this->sqs->deleteMessageBatch([
            'QueueUrl' => $this->queue,
            'Entries' => $entries->values()->toArray(),
        ]);

        foreach ($response['Successful'] as $message) {
            $this->jobs[$message['Id']]->setDeleted();
        }

        $this->deleted = true;
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int   $delay
     * @return void
     */
    public function release($delay = 0)
    {
        $entries = collect($this->jobs)->transform(function (SqsJob $job) use ($delay) {
            return [
                'Id' => $job->getJobId(),
                'ReceiptHandle' => $job->getReceiptHandle(),
                'VisibilityTimeout' => $delay,
            ];
        });

        $response = $this->sqs->changeMessageVisibilityBatch([
            'QueueUrl' => $this->queue,
            'Entries' => $entries->values()->toArray(),
        ]);

        foreach ($response['Successful'] as $message) {
            $this->jobs[$message['Id']]->setDeleted();
        }
    }
}
