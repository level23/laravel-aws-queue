<?php

namespace Level23\AwsQueue\Tests;

use Level23\AwsQueue\Queue\BatchQueue;
use Level23\AwsQueue\Queue\Connectors\BatchConnector;
use PHPUnit\Framework\TestCase;

class BatchConnectorTest extends TestCase
{

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
