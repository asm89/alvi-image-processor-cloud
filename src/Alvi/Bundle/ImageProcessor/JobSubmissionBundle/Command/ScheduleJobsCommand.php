<?php

namespace Alvi\Bundle\ImageProcessor\JobSubmissionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Vincent <vincentvanbeek@mac.com>
 */
class ScheduleJobsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('alvi:image-processor:jobSubmit')
            ->setDescription('Schedule incoming image process jobs.')
            ->setDefinition(array(
                new InputOption('workloadSize', '', InputOption::VALUE_OPTIONAL, "Amount of jobs to be submitted.", 3000),
                new InputOption('normalJobSize', '', InputOption::VALUE_OPTIONAL, "Normal job size in microseconds.", 1000000),
                new InputOption('burstJobSize', '', InputOption::VALUE_OPTIONAL, "Burst job size in microseconds.", 1000000),
                new InputOption('burstInterval', '', InputOption::VALUE_OPTIONAL, "Burst interval number of normal message in between bursts.", 600),
                new InputOption('burstCount', '', InputOption::VALUE_OPTIONAL, "Number of bursts.", 0),
                new InputOption('burstSize', '', InputOption::VALUE_OPTIONAL, "Number of jobs in a burst.", 600),
                new InputOption('jobInterupt', '', InputOption::VALUE_OPTIONAL, "Normal job interrupt in microseconds.", 300000),
                new InputOption('recordWorkload', '', InputOption::VALUE_OPTIONAL, "Record the workload pattern for later use.", false),
                new InputOption('openWorkload', '', InputOption::VALUE_OPTIONAL, "Read the workload pattern from a file.", false),
                new InputOption('workloadFilepath', '', InputOption::VALUE_OPTIONAL, "Record filepath", '/data/workload.log'),
            ))
                ->setHelp(<<<EOT
The <info>%command.name%</info> command will schedule incoming image processing jobs.

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

        //workload size (number of jobs submitted, bursts count as 1)
        $workloadSize = $input->getOption('workloadSize');
        $workloadSizeCounter = 0;

        //if write workload to file create file or erase file
        $workloadFilepath = $input->getOption('workloadFilepath');
        $this->eraseCreateFile($input->getOption('recordWorkload'), $workloadFilepath);
        
        //normal message
        //job size in microseconds
        $normalJobSize = $input->getOption('normalJobSize');
        //job size in microseconds

        //burst job
        //job size in microseconds
        $burstJobSize = $input->getOption('burstJobSize');
        
        $jobCounter = 0;
        //burst interval
        $burstInterval = $input->getOption('burstInterval');
        //number of bursts
        $burstCount = $input->getOption('burstCount');
        //burst size
        $burstSize = $input->getOption('burstSize');
        //normal job timing in microseconds
        $jobInterupt = $input->getOption('jobInterupt');

        //connection with graphite
        $collector = $container->get('beberlei_metrics.collector.statsd');
        
        //rabbitMQ
        $rabbitMQ = $container->get('old_sound_rabbit_mq.upload_picture_producer');
        
        
        //if read workoad from file is true
        //replay a recorded workload
        if($input->getOption('openWorkload') == true) {
            $workload = $this->readWorkloadFromFile($workloadFilepath);
            foreach($workload as $job) {
                if($job['jobInterupt'] != 0) {
                    usleep($job['jobInterupt']);
                }
                $job['submitTime'] = microtime(true);
                $rabbitMQ->publish(serialize($job));
                $collector->increment('alvi.jobs');
                //send stats to graphite
                $collector->flush();
            }
        }
        //else generate workload
        else {
            //count the number of bursts
            $burstCounter = 0;
            while($workloadSizeCounter < $workloadSize)
            {
                $jobNormal = array('user_id' => 'normal', 'image_path' => '/path/to/new/pic.png', 'size' => (stats_dens_normal(rand(-5,5),0,1)*$normalJobSize), 'submitTime' => null, 'jobInterupt' => $jobInterupt);
                //job interupt
                usleep($jobInterupt);
                if($jobCounter == $burstInterval)
                {
                    if($burstCounter < $burstCount) {
                        //add message burst
                        for($i=0;$i<$burstSize;$i++)
                        {
                            $jobBurst = array('user_id' => 'burst', 'image_path' => '/path/to/new/pic.png', 'size' => (stats_dens_normal(rand(-5,5),0,1)*$burstJobSize), 'submitTime' => null, 'jobInterupt' => 0);
                            if($input->getOption('recordWorkload') == true) {
                                $this->recordJob($jobBurst, $workloadFilepath);
                            }
                            $jobBurst['submitTime'] = microtime(true);
                            $rabbitMQ->publish(serialize($jobBurst));
                            $collector->increment('alvi.jobs');
                            //send stats to graphite
                            $collector->flush();
                        }
                        $burstCounter++;
                    }
                    //reset job counter
                    $jobCounter = 0;
                }
                else
                {
                    if($input->getOption('recordWorkload') == true) {
                        $this->recordJob($jobNormal, $workloadFilepath);
                    }
                    //add submit time to job
                    $jobNormal['submitTime'] = microtime(true);
                    //send normal message
                    $rabbitMQ->publish(serialize($jobNormal));
                    $collector->increment('alvi.jobs');
                    //send stats to graphite
                    $collector->flush();

                    //increment jobcounter
                    $jobCounter++;
                }
                //increment workloadsize counter
                $workloadSizeCounter++;
            }
        }
    }
    
    /**
     * @param job array $job
     */
    private function recordJob($job, $workloadFilepath) {
        $file = fopen($workloadFilepath, 'a');
        fwrite($file,json_encode($job)."\n");
        fclose($file);
    }
    
    /**
     * return array with jobs
     */
    private function readWorkloadFromFile($workloadFilepath) {
        $file = fopen($workloadFilepath, 'r');
        $workload = array();
        while($line = fgets($file)) {
            $workload[] = json_decode($line, true);
        }
        return $workload;
    }
    /**
     * erase and/or create the record file if 'recordWorkload' is true
     */
    private function eraseCreateFile($recordWorkload, $workloadFilepath) {
        if($recordWorkload == true) {
            if(file_exists($workloadFilepath)) {
                unlink($workloadFilepath);
            }
            $file = fopen($workloadFilepath, 'w');
            fclose($file);
        }
    }
}