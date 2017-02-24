<?php

namespace Level23\AwsQueue\Queue;

trait JobParser
{
    public function parseJobMessage(array $message)
    {
        $message['Body'] = json_decode($message['Body'], true);

        // If there is no subject available its an sqs
        if (isset($message['Body']['Subject'])) {
            $message['Body'] = $this->createStringPayload(
                $message['Body']['Subject'],
                json_decode($message['Body']['Message'], true)
            );
        }

        return $message;
    }

    /**
     * Create a typical, string based queue payload array.
     *
     * @param  string  $job
     * @param  mixed  $data
     * @return array
     */
    protected function createStringPayload($job, $data)
    {
        return [
            'displayName' => is_string($job) ? explode('@', $job)[0] : null,
            'job' => $job, 'maxTries' => null,
            'timeout' => null, 'data' => $data,
        ];
    }

}