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
        $entries = [];

        foreach ($this->jobs as $job) {
            $entries[] = [
                'Id' => $job->getJobId(),
                'ReceiptHandle' => $job->getReceiptHandle()
            ];
        }

        echo 'Deleting '.count($entries) . ' messages'.PHP_EOL;

        $response = $this->sqs->deleteMessage([
            'QueueUrl' => $this->queue,
            'Entries' => $entries,
        ]);

        echo 'Deleted '.count($response['Successful']) . ' messages'.PHP_EOL;

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
        $entries = [];

        foreach ($this->jobs as $job) {
            $entries[] = [
                'Id' => $job->getJobId(),
                'ReceiptHandle' => $job->getReceiptHandle(),
                'VisibilityTimeout' => $delay,
            ];
        }

        echo 'Releasing '.count($entries) . ' messages'.PHP_EOL;

        $response = $this->sqs->changeMessageVisibilityBatch([
            'QueueUrl' => $this->queue,
            'Entries' => $entries,
        ]);

        echo 'Released '.count($response['Successful']) . ' messages'.PHP_EOL;

        foreach ($response['Successful'] as $message) {
            $this->jobs[$message['Id']]->setDeleted();
        }
    }
}