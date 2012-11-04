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
        //connection with rabbitMQ
        $username = 'guest';
        $password = 'guest';
        $context = stream_context_create(array(
            'http' => array(
                'header'  => "Authorization: Basic " . base64_encode("$username:$password")
            )
        ));
        
       while(true) {
            //TODO fix static ip
            $rabbitMQjsonResponse = file_get_contents("http://172.16.1.23:55672/api/queues/%2f/upload-picture", false, $context);
            $rabbitMQqueueData = json_decode($rabbitMQjsonResponse, true);
            $collector->timing('alvi.queue.size.upload-picture.',$rabbitMQqueueData['messages_ready']);
            if(isset($rabbitMQqueueData['incoming']['stats']['publish_details']['rate'])) {
                $collector->timing('alvi.queue.incomming_rate.upload-picture',$rabbitMQqueueData['incoming']['stats']['publish_details']['rate']);
            }
            else{
                $collector->timing('alvi.queue.incomming_rate.upload-picture',0);
            }
            if(isset($rabbitMQqueueData['deliveries']['stats']['publish_details']['rate'])) {
                $collector->timing('alvi.queue.delivery_rate.upload-picture',$rabbitMQqueueData['deliveries']['stats']['publish_details']['rate']);
            }
            else{
                $collector->timing('alvi.queue.delivery_rate.upload-picture',0);
            }
            //send stats to graphite
            $collector->flush();
            sleep(1);
        }
    }
}