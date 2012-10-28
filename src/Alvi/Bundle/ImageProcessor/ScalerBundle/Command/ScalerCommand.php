<?php

namespace Alvi\Bundle\ImageProcessor\ScalerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Vincent <vincentvanbeek@mac.com>
 */
class ScalerCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('alvi:image-processor:scaler')
            ->setDescription('Start and stop VM\'s.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command will scale the workers in the cloud.

  <info>php %command.full_name%</info>
EOT
            );
        }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //TODO: get from zookeeper
        //Graphite server
        $graphiteUrl = "http://192.168.56.23/render";
        //graphite command for retrieving the moving average of the finish times
        $commandFinishTime = "?target=movingAverage(stats.timers.alvi.jobs_finish_time.mean,10)&format=json&from=-1minutes";
        //graphite command for retrieving the moving average of the process times
        $commandProcessTime = "?target=movingAverage(stats.timers.alvi.jobs_process_time.mean,10)&format=json&from=-1minutes";

        $container = $this->getContainer();
        $collector = $container->get('beberlei_metrics.collector.statsd');
        //rabbitMQ
        $rabbitMQ = $container->get('old_sound_rabbit_mq.scaler_producer');

        //Scale up message
        $scaleUp = array('scale' => 'up');
        //Scale down message
        $scaleDown = array('scale' => 'down');

        while (true) {
            //wait 10 seconds and rescale the system.
            sleep(10);
            //If already scaling down or up wait for this process to finish
            //TODO: get from zookeeper
            $scaling = false;
            if (!$scaling) {
                //retrieve the raw json data from graphite
                $graphiteFinishTimeData = json_decode(file_get_contents($graphiteUrl.$commandFinishTime));
                $graphiteProcessTimeData = json_decode(file_get_contents($graphiteUrl.$commandProcessTime));

                //calculate the average finish time
                $averageFinishTime = 1;
                $i = 0;
                foreach ($graphiteFinishTimeData[0]->datapoints as $finishTime) {
                    if (isset($finishTime[0]) && $finishTime[0] != "") {
                        $averageFinishTime += $finishTime[0];
                        $i++;
                    }
                }
                if ($i != 0) {
                    $averageFinishTime = $averageFinishTime/$i;
                } else {
                    $averageFinishTime = 1;
                }

                //calculate the average process time of a job
                $averageProcessTime = 1;
                $i = 0;
                foreach ($graphiteProcessTimeData[0]->datapoints as $processTime) {
                    if (isset($processTime[0]) && $processTime[0] != "") {
                        $averageProcessTime += $processTime[0];
                        $i++;
                    }
                }

                if ($i != 0) {
                    $averageProcessTime = $averageProcessTime/$i;
                } else {
                    $averageFinishTime = 3;
                }

                //if the finish time is more than 5 times the process time scale up
                if ($averageFinishTime/$averageProcessTime > 5) {
                    //scale up
                    $rabbitMQ->publish(serialize($scaleUp));

                }
                //if the finish time is less than 2 times the process time scale down
                elseif ($averageFinishTime/$averageProcessTime < 2) {
                    //scale down
                    $rabbitMQ->publish(serialize($scaleDown));
                }
                //do nothing the system is operating at the prefered scale
                else {
                    //do nothing
                }
            }
        }
    }
}
