<?php

namespace Level23\AwsQueue\Tests;

use Aws\Sqs\SqsClient;
use Illuminate\Container\Container;
use Level23\AwsQueue\Queue\BatchQueue;
use Level23\AwsQueue\Queue\Jobs\BatchJob;
use Level23\AwsQueue\Queue\Jobs\SqsJob;
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

    /**
     * @var
     */
    protected $expectedMessageSecond;

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

        $this->expectedMessageSecond = [
            'job' => 'test_2',
            'data' => ['key' => 'value','test' => 'test value']
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
                ['MessageId' => 1,'Body' => json_encode(['job' => 'test', 'data' => ['key' => 'value']])],
                ['MessageId' => 2,'Body' => json_encode(['job' => 'test_2', 'data' => ['key' => 'value','test' => 'test value']])]
            ]
        ]);

        $queue = new BatchQueue($this->client, 'default');
        $queue->setContainer($this->container);

        /** @var BatchJob $batchJob */
        $batchJob = $queue->pop();
        $this->assertInstanceOf(BatchJob::class, $batchJob);

        $jobs = $batchJob->getJobs();

        $this->assertInstanceOf(SqsJob::class, $jobs[1]);
        $this->assertInstanceOf(SqsJob::class, $jobs[2]);

        $this->assertEquals($jobs[1]->getRawBody(), $this->expectedMessage);
        $this->assertEquals($jobs[2]->getRawBody(), $this->expectedMessageSecond);
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
