<?php

namespace Level23\AwsQueue\Tests;

use Aws\Sqs\SqsClient;
use Illuminate\Container\Container;
use Illuminate\Queue\Jobs\SqsJob;
use Level23\AwsQueue\Queue\AwsConnector;
use Level23\AwsQueue\Queue\AwsQueue;
use Level23\AwsQueue\Queue\BatchQueue;
use Level23\AwsQueue\Queue\Connectors\BatchConnector;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class AwsConnectorTest extends TestCase
{

    public function testCanConnectToQueue()
    {
        $connector = new AwsConnector();

        $queue = $connector->connect([
            'key' => 'dummy_key',
            'secret' => 'dummy_secret',
            'region' => 'eu-central-1',
            'queue' => '',
        ]);

        $this->assertInstanceOf(AwsQueue::class, $queue);
    }

    public function testCanConnectToBatchQueue()
    {
        $connector = new BatchConnector();

        $queue = $connector->connect([
            'key' => 'dummy_key',
            'handler' => null,
            'max' => 10,
            'secret' => 'dummy_secret',
            'region' => 'eu-central-1',
            'queue' => '',
        ]);

        $this->assertInstanceOf(BatchQueue::class, $queue);
    }
}
