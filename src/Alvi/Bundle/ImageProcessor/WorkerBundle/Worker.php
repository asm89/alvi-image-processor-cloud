<?php

namespace Alvi\Bundle\ImageProcessor\WorkerBundle;

use Beberlei\Metrics\Collector\Collector;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * @author Alexander <iam.asm89@gmail.com>
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

        sleep(mt_rand(20, 2000) / 1000);

        echo $msg->delivery_info['delivery_tag'] . " ";
        echo $msg->body . "\n";

        $this->collector->increment('alvi.jobs_processed');

        $diff  = microtime(true) - $start;
        $this->collector->timing('alvi.jobs_process_time', $diff);

        $this->collector->flush();
    }
}
