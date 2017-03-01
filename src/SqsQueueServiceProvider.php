<?php

namespace Level23\AwsQueue;

use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use Level23\AwsQueue\Queue\Connectors\BatchConnector;
use Level23\AwsQueue\Queue\Connectors\SqsConnector;

class SqsQueueServiceProvider extends ServiceProvider
{
    /**
     * Boot the service
     */
    public function boot()
    {
        $this->registerAwsConnector($this->app['queue']);
        $this->registerBatchConnector($this->app['queue']);
    }

    /**
     * Register the AWS Queue Connection
     *
     * @param QueueManager $manager
     */
    protected function registerAwsConnector(QueueManager $manager)
    {
        $manager->addConnector('sqs', function() {
            return new SqsConnector;
        });
    }

    /**
     * Register the AWS Queue Connection
     *
     * @param QueueManager $manager
     */
    protected function registerBatchConnector(QueueManager $manager)
    {
        $manager->addConnector('sqs-batch', function() {
            return new BatchConnector;
        });
    }
}
