<?php

namespace Level23\AwsQueue\Queue\Connectors;

use Aws\Sqs\SqsClient;
use Illuminate\Support\Arr;
use Level23\AwsQueue\Queue\BatchQueue;

class BatchConnector extends SqsConnector
{
    /**
     * Establish a queue connection.
     *
     * @param  array $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        $config      = $this->getConfig($config);
        $handler     = Arr::pull($config, 'handler');
        $maxMessages = Arr::pull($config, 'max', 1);
        $queue       = new BatchQueue(new SqsClient($config), $config['queue'], Arr::get($config, 'prefix', ''));

        return $queue->setHandlerClass($handler)->setMaxMessages($maxMessages);
    }
}
