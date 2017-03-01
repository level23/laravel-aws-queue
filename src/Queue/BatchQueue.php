<?php

namespace Level23\AwsQueue\Queue;

use Level23\AwsQueue\Queue\Jobs\BatchJob;
use Level23\AwsQueue\Queue\Jobs\SqsJob;

class BatchQueue extends SqsQueue
{
    /**
     * @var string|null
     */
    protected $handlerClass;

    /**
     * @var int
     */
    protected $maxMessages = 10;

    /**
     * Pop the next job off of the queue.
     *
     * @param  string  $queue
     * @return null|BatchJob
     */
    public function pop($queue = null)
    {
        $queueUrl = $this->getQueue($queue);

        $response = $this->sqs->receiveMessage([
            'QueueUrl' => $queueUrl,
            'AttributeNames' => ['ApproximateReceiveCount'],
            'MaxNumberOfMessages' => $this->maxMessages
        ]);

        if (!$this->hasMessages($response)) {
            return null;
        }

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

    /**
     * @param int $max
     * @return $this
     */
    public function setMaxMessages(int $max = null)
    {
        if(is_int($max)) {
            $this->maxMessages = $max;
        }

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
     * @param string|null $queue
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
