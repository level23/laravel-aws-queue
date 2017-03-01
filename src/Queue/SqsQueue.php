<?php

namespace Level23\AwsQueue\Queue;

use Illuminate\Queue\SqsQueue as LaravelSqsQueue;
use Level23\AwsQueue\Queue\Jobs\SqsJob;

class SqsQueue extends LaravelSqsQueue
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

        if (!$this->hasMessages($response)) {
            return null;
        }

        $message = $this->parseJobMessage($response['Messages'][0]);

        return new SqsJob(
            $this->container,
            $this->sqs,
            $message,
            $this->connectionName,
            $queue
        );
    }

    /**
     * Parse the job message
     *
     * @param array $message
     * @return array
     */
    protected function parseJobMessage(array $message)
    {
        $message['Body'] = json_decode($message['Body'], true);

        // If there is no subject available its an sqs
        if (isset($message['Body']['Subject'])) {
            $message['Body'] = $this->createStringPayload(
                $message['Body']['Subject'],
                json_decode($message['Body']['Message'], true)
            );
        }

        return $message;
    }

    /**
     * Check if response has messages
     *
     * @param $response
     * @return bool
     */
    protected function hasMessages($response)
    {
        if (isset($response['Messages']) && is_array($response['Messages']) && count($response['Messages']) > 0) {
            return true;
        }

        return false;
    }
}
