<?php

namespace Level23\AwsQueue\Queue;

use Illuminate\Queue\SqsQueue;
use Level23\AwsQueue\Queue\Jobs\BatchJob;
use Level23\AwsQueue\Queue\Jobs\SqsJob;

class BatchQueue extends SqsQueue
{
    use JobParser;

    /**
     * @var string|null
     */
    protected $handlerClass;

    /**
     * @var int
     */
    protected $maxMessages = 1;

    /**
     * Pop the next job off of the queue.
     *
     * @param  string  $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        $queueUrl = $this->getQueue($queue);

        $response = $this->sqs->receiveMessage([
            'QueueUrl' => $queueUrl,
            'AttributeNames' => ['ApproximateReceiveCount'],
            'MaxNumberOfMessages' => $this->maxMessages
        ]);

        if(!isset($response['Messages']) or is_null($response['Messages'])) {
            return null;
        }

        if (count($response['Messages']) > 0) {
            $batchMessage = [
                'MessageId' => null,
                'Body' => $this->createStringPayload($this->getHandler($queue), [])
            ];

            $batchJob = new BatchJob($this->container, $this->sqs, $batchMessage, $this->connectionName, $queueUrl);

            foreach ($response['Messages'] as $message) {
                $message = $this->parseJobMessage($message);

                $batchJob->pushJob(new SqsJob(
                    $this->container,
                    $this->sqs,
                    $message,
                    $this->connectionName,
                    $queueUrl
                ));
            }

            return $batchJob;
        }
    }

    /**
     * @param int $max
     * @return $this
     */
    public function setMaxMessages(int $max = 1)
    {
        $this->maxMessages = $max;

        return $this;
    }

    /**
     * @param $handler
     * @return $this
     */
    public function setHandlerClass($handler = null)
    {
        $this->handlerClass = $handler;

        return $this;
    }

    /**
     * @param $queue
     * @return string
     */
    public function getHandler($queue)
    {
        if ($this->handlerClass) {
            return $this->handlerClass;
        }

        return 'batch-'.$queue;
    }
}
