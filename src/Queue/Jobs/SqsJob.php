<?php

namespace Level23\AwsQueue\Queue\Jobs;

use Illuminate\Queue\Jobs\SqsJob as LaravelSqsJob;

class SqsJob extends LaravelSqsJob
{
    /**
     * Get the decoded body of the job.
     *
     * @return array
     */
    public function payload()
    {
        return $this->getRawBody();
    }

    /**
     * Get the raw body string for the job.
     *
     * @return array
     */
    public function getRawBody()
    {
        return $this->job['Body'];
    }

    /**
     * Get the ReceiptHandle for the job.
     *
     * @return string
     */
    public function getReceiptHandle()
    {
        return $this->job['ReceiptHandle'];
    }

    /**
     * Set the job to deleted
     */
    public function setDeleted()
    {
        $this->deleted = true;
    }

    /**
     * Set the job to released
     */
    public function setReleased()
    {
        $this->released = true;
    }
}
