<?php

namespace Level23\AwsQueue\Queue;

use Illuminate\Queue\SqsQueue;
use Level23\AwsQueue\Queue\Jobs\BatchJob;
use Level23\AwsQueue\Queue\Jobs\SqsJob;

class BatchQueue extends SqsQueue
{
    use JobParser;

    /**
     * Pop the next job off of the queue.
     *
     * @param  string  $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        $response = $this->sqs->receiveMessage([
            'QueueUrl' => $queue = $this->getQueue($queue),
            'AttributeNames' => ['ApproximateReceiveCount'],
        ]);

        echo 'Popping '.count($response['Messages']) . ' messages'.PHP_EOL;

        if (count($response['Messages']) > 0) {

            $batchMessage = [
                'MessageId' => null,
                'Body' => $this->createStringPayload('batch-'.$queue,[])
            ];

            $batchJob = new BatchJob($this->container, $this->sqs, $batchMessage, $this->connectionName, $queue);

            foreach ($response['Messages'] as $message) {

                $message = $this->parseJobMessage($message);

                $batchJob->pushJob(new SqsJob(
                    $this->container, $this->sqs, $message,
                    $this->connectionName, $queue
                ));
            }

            return $batchJob;
        }
    }

}