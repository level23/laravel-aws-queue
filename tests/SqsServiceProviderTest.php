<?php

namespace Level23\AwsQueue\Tests;

use Level23\AwsQueue\Queue\BatchQueue;
use Level23\AwsQueue\Queue\SqsQueue;
use Level23\AwsQueue\SqsQueueServiceProvider;
use Orchestra\Testbench\TestCase;

class SqsServiceProviderTest extends TestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [SqsQueueServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('queue.connections.sqs', [
            'driver' => 'sqs',
            'key' => '',
            'secret' => '',
            'queue' => '',
            'region' => ''
        ]);

        $app['config']->set('queue.connections.sqs-batch', [
            'driver' => 'sqs-batch',
            'key' => '',
            'secret' => '',
            'queue' => '',
            'region' => ''
        ]);

        $app['config']->set('queue.default', 'sqs');
    }

    /**
     * Test if the connection loaded for sqs is our new one
     */
    public function testSqsDriverSuccessfullyLoaded()
    {
        $connection = $this->app['queue']->connection();

        $this->assertInstanceOf(SqsQueue::class, $connection);
    }

    /**
     * Test if the connection loaded for sqs is our new one
     */
    public function testBatchDriverSuccessfullyLoaded()
    {
        $connection = $this->app['queue']->connection('sqs-batch');

        $this->assertInstanceOf(BatchQueue::class, $connection);
    }
}
