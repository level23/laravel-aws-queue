<?php

namespace Level23\AwsQueue\Tests;

use Level23\AwsQueue\AwsQueueServiceProvider;
use Level23\AwsQueue\Queue\AwsConnector;
use Level23\AwsQueue\Queue\AwsQueue;
use Mockery;
use Orchestra\Testbench\TestCase;

class AwsServiceProviderTest extends TestCase
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
        return [AwsQueueServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('queue.connections.sqs', [
            'driver' => 'aws',
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
    public function testAWSDriverSuccessfullyLoaded()
    {
        $connection = $this->app['queue']->connection();

        $this->assertInstanceOf(AwsQueue::class, $connection);
    }
}
