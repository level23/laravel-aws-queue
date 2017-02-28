<?php

namespace Level23\AwsQueue\Tests;

use Level23\AwsQueue\Queue\Connectors\SqsConnector;
use Level23\AwsQueue\Queue\SqsQueue;
use PHPUnit\Framework\TestCase;

class SqsConnectorTest extends TestCase
{

    public function testCanConnectToQueue()
    {
        $connector = new SqsConnector();

        $queue = $connector->connect([
            'key' => 'dummy_key',
            'secret' => 'dummy_secret',
            'region' => 'eu-central-1',
            'queue' => '',
        ]);

        $this->assertInstanceOf(SqsQueue::class, $queue);
    }
}
