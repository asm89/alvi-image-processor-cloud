<?php

namespace Alvi\Bundle\ImageProcessor\WorkerBundle;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * @author Alexander <iam.asm89@gmail.com>
 */
class Worker implements ConsumerInterface
{
    public function execute(AMQPMessage $msg)
    {
        echo $msg->delivery_info['delivery_tag'] . " ";
        echo $msg->body . "\n";
    }
}
