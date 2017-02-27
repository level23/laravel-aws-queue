[![Build Status](https://travis-ci.org/level23/laravel-aws-queue.svg?branch=master)](https://travis-ci
.org/level23/laravel-aws-queue)

# Laravel AWS Queue
This package adds a connection driver for sqs to allow for messages send by AWS SNS(Simple Notification Service) to 
SQS to be parsed correctly.

The Subject of an SNS message is used for the job name and the Message as the body/data for the job

## Installing

Install the latest version with:

```bash
$ composer require level23/laravel-aws-queue
```

And then add the following service provider to 

### Usage

Add the following line to your `config/app.php`

##### Laravel
```php
'providers' => [
    ...
    Level23\AwsQueue\AwsQueueServiceProvider::class
]
```

##### Lumen
```php
$app->register(Level23\AwsQueue\AwsQueueServiceProvider::class);
```

Then in your config/queue.php set the driver for sqs to use our new driver named `aws`
```php
'sqs' => [
    'driver' => 'aws', //default is sqs
    'key' => env('AWS_SQS_KEY'),
    'secret' => env('AWS_SQS_SECRET'),
    'prefix' => env('AWS_SQS_PREFIX'),
    'queue' => env('AWS_SQS_QUEUE'),
    'region' => env('AWS_SQS_REGION'),
]
```

## Batch jobs

To receive batch jobs change the driver to `aws-batch` and add the following to the config
```php
'sqs' => [
    'driver' => 'aws-batch', //default is sqs
    'max' => 10,
    'handler' => 'Classname or binding name in ioc'
    ...
]
```

This method gives you the ability to receive messages in batch and handle them at once!
The maximum AWS allows us is 10 messages per request

## Requirements

To make use if this package you have to run PHP 7.0 or higher.

## Contributing

If you want to help us improve this implementation, just contact us. All help is welcome!
The only requirement for contributing is that all code is 100% covered by unit tests and that they implement the 
PSR standards.

## License

See the file LICENSE for more information.