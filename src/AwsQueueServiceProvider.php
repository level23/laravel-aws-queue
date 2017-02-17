<?php

namespace Level23\AwsQueue;

use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use Level23\AwsQueue\Queue\AwsConnector;

class AwsQueueServiceProvider extends ServiceProvider
{
    /**
     * Boot the service
     */
    public function boot()
    {
        $this->registerAwsConnector($this->app['queue']);
    }

    /**
     * Register the AWS Queue Connection
     *
     * @param QueueManager $manager
     */
    protected function registerAwsConnector(QueueManager $manager)
    {
        $manager->addConnector('aws', function() {
            return new AwsConnector;
        });
    }
}