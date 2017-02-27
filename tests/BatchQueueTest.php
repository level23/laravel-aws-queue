<?php

namespace Level23\AwsQueue\Tests;

use Aws\Sqs\SqsClient;
use Illuminate\Container\Container;
use Illuminate\Queue\Jobs\SqsJob;
use Level23\AwsQueue\Queue\AwsQueue;
use Level23\AwsQueue\Queue\BatchQueue;
use Level23\AwsQueue\Queue\Jobs\BatchJob;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class BatchQueueTest extends TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|SqsClient
     */
    protected $client;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Container
     */
    protected $container;

    /**
     * @var
     */
    protected $expectedMessage;

    protected function setUp()
    {
        $this->container = $this->createMock(Container::class);

        $this->client = $this->getMockBuilder(SqsClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['receiveMessage'])
            ->getMock();

        $this->expectedMessage = [
            'job' => 'test',
            'data' => ['key' => 'value']
        ];
    }

    public function testWillCallReceiveMessage()
    {
        $this->client->expects($this->once())
            ->method('receiveMessage');
        $queue = new BatchQueue($this->client, 'default');
        $queue->setContainer($this->container);
        $queue->pop();
    }

    public function testCanReceiveSQSMessage()
    {
        $this->client->method('receiveMessage')->willReturn([
            'Messages' => [
                ['Body' => json_encode(['job' => 'test', 'data' => ['key' => 'value']])]
            ]
        ]);

        $queue = new AwsQueue($this->client, 'default');
        $queue->setContainer($this->container);

        $job = $queue->pop();

        $this->assertInstanceOf(SqsJob::class, $job);
        $this->assertEquals(json_decode($job->getRawBody(), true), $this->expectedMessage);
    }

    public function testCanReceiveSNSMessage()
    {
        $this->client->method('receiveMessage')->willReturn([
            'Messages' => [
                [
                    'MessageId' => 1,
                    'Body' => json_encode([
                        'Subject' => 'test',
                        'Message' => json_encode(['key' => 'value'])
                    ])
                ],
                [
                    'MessageId' => 2,
                    'Body' => json_encode([
                        'Subject' => 'test',
                        'Message' => json_encode(['key' => 'value'])
                    ])
                ]
            ]
        ]);

        $queue = new BatchQueue($this->client, 'default');
        $queue->setContainer($this->container);

        /** @var BatchJob $job */
        $job = $queue->pop();

        $this->assertInstanceOf(BatchJob::class, $job);
        $this->assertCount(2, $job->getJobs());
    }
}
