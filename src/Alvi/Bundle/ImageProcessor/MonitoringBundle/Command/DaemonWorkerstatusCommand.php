<?php

namespace Alvi\Bundle\ImageProcessor\MonitoringBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Alvi\Bundle\ImageProcessor\ProvisionerBundle\VirtualMachine;


/**
 * Check if the workers are still running, if not remove from zookeeper and send spindown message
 *
 * @author Vincent <vincentvanbeek@mac.com>
 */
class DaemonWorkerstatusCommand extends ContainerAwareCommand
{
    private $graphiteAPI;
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('alvi:image-processor:daemon:workerstatus')
            ->setDescription('Start monitoring daemon for Worker Status.')
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
        //Graphite api
        $this->graphiteAPI = $container->get('alvi.image_processor.scaler.policy.GraphiteAPI');
        $virtualMachineManager = $container->get('alvi.image_processor.provisioner.manager');
        $statsd = $container->get('beberlei_metrics.collector.statsd');
        while(true) {
            $workers = $virtualMachineManager->getRunningVirtualMachinesByType('worker');
            foreach($workers as $worker) {
                $command = "?target=movingAverage(stats.timers.alvi.queue.stats.alvi.heartbeat.".$worker->getFqdn().",10)&format=json&from=-1minutes";
                $heartbeat = $this->executeAverageCommand($command);
                //if hearbeat is false, graphite could not be reached.
                if($heartbeat !== false && $heartbeat < 0.8) {
                    $virtualMachineManager->setMachineState($worker, VirtualMachine::STATE_FAILED);
                }
            }
            $statsd->increment('alvi.heartbeat.workerstatus');
            //send stats to graphite
            $statsd->flush();
            //check every 5 seconds is the workers are still running.
            sleep(1);
        }
    }
    /**
     * @param String $command (Graphite rest API command)
     * return int moving 
     */
    private function executeAverageCommand($command) {
        $averageJsonData = $this->graphiteAPI->getDataFromGraphiteCommand($command);
        if(isset($averageJsonData[0]) && isset($averageJsonData[0]->datapoints)) {
            $average = $this->calculateAverage($averageJsonData[0]->datapoints);   
        }
        else {
            return false;
        }
        return $average;
    }
    
    /**
     * @param datapoints array $data
     * return int average if more than 0 measurements other wise return false
     */
    private function calculateAverage($data) {
        //calculate the average finish time
        $total = 0;
        $i = 0;
        foreach ($data as $item) {
            if(isset($item[0]) && ($item[0] == '0' || $item[0] >= 1)){
                $total += $item[0];
                $i++;
            }
        }
        if ($i != 0) {
            $average = $total/$i;
        } else {
            return 0;
        }
        return $average;
    }
}