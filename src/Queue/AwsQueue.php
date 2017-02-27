<?php

namespace Level23\AwsQueue\Queue;

use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Queue\SqsQueue;

class AwsQueue extends SqsQueue
{
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

        if (count($response['Messages']) > 0) {
            $message = $this->parseJobMessage($response['Messages'][0]);

            return new SqsJob(
                $this->container,
                $this->sqs,
                $message,
                $this->connectionName,
                $queue
            );
        }
    }

    protected function parseJobMessage(array $message)
    {
        $body = json_decode($message['Body'], true);

        // If there is no subject available its an sqs
        if (isset($body['Subject'])) {
            $message['Body'] = json_encode(
                $this->createStringPayload(
                    $body['Subject'],
                    json_decode($body['Message'], true)
                )
            );
        }

        return $message;
    }
}
