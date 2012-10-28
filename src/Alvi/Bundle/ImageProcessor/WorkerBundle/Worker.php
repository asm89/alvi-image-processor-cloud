<?php

namespace Alvi\Bundle\ImageProcessor\WorkerBundle;

use Beberlei\Metrics\Collector\Collector;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * @author Alexander <iam.asm89@gmail.com>
 * @author Vincent <vincentvanbeek@mac.com>
 */
class Worker implements ConsumerInterface
{
    private $collector;

    public function __construct(Collector $collector)
    {
        $this->collector = $collector;
    }

    public function execute(AMQPMessage $msg)
    {
        $start = microtime(true);

        $job = unserialize($msg->body);
        usleep($job['size']);

        $this->collector->increment('alvi.jobs_processed');

        $diff  = microtime(true) - $start;
        $this->collector->timing('alvi.jobs_process_time', $diff);

        $this->collector->flush();
    }
}
