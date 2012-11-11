<?php

namespace Alvi\Bundle\ImageProcessor\MonitoringBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * @author Vincent <vincentvanbeek@mac.com>
 */
class DaemonQueueInfoCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('alvi:image-processor:daemon:queueSize')
            ->setDescription('Start monitoring daemon for queue information.')
                ->setHelp(<<<EOT
The <info>%command.name%</info> command will start a monitoring deamon.

  <info>php %command.full_name%</info>
EOT
            );
        }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        //connection with graphite
        $collector = $container->get('beberlei_metrics.collector.statsd');
        
        //RabbitMQ api
        $RabbitMQAPI = $container->get('alvi.image_processor.scaler.policy.rabbitmqapi');
        
        while(true) {
            $rabbitMQqueueData = $RabbitMQAPI->executeApiCall("queues/%2f/upload-picture");
            if($rabbitMQqueueData == false) {
                //something went wrong with the api command
            }
            else {
                $collector->timing('alvi.queue.size.upload-picture.',$rabbitMQqueueData['messages']);
    
                if(isset($rabbitMQqueueData['incoming'][0]['stats']['publish_details']['rate'])) {
                    $collector->timing('alvi.queue.incoming_rate.upload-picture',$rabbitMQqueueData['incoming'][0]['stats']['publish_details']['rate']);
                }
                else{
                    $collector->timing('alvi.queue.incoming_rate.upload-picture',0);
                }
                if(isset($rabbitMQqueueData['message_stats']['deliver_details']['rate'])) {
                    $collector->timing('alvi.queue.delivery_rate.upload-picture',$rabbitMQqueueData['message_stats']['deliver_details']['rate']);
                }
                else{
                    $collector->timing('alvi.queue.delivery_rate.upload-picture',0);
                }
                //send stats to graphite
                $collector->flush();
            }
            sleep(0.1);
        }
    }
}
