<?php

namespace Alvi\Bundle\ImageProcessor\MonitoringBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * @author Vincent <vincentvanbeek@mac.com>
 */
class DaemonHeartbeatCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('alvi:image-processor:daemon:heartbeat')
            ->setDescription('Start monitoring daemon for heartbeat.')
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
        while(true) {
            $collector->increment('alvi.heartbeat'.gethostname());
            $collector->increment('alvi.heartbeat.all');
            //send stats to graphite
            $collector->flush();
            sleep(1);
        }
    }
}