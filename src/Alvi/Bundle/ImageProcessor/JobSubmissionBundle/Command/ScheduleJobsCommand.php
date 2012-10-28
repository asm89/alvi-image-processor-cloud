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
                new InputOption('workloadSize', 'ws', InputOption::VALUE_OPTIONAL, "Amount of jobs to be submitted.", 1000),
                new InputOption('normalJobSize', 'njs', InputOption::VALUE_OPTIONAL, "Normal job size in microseconds.", 1000000),
                new InputOption('burstJobSize', 'bjs', InputOption::VALUE_OPTIONAL, "Burst job size in microseconds.", 100000),
                new InputOption('burstInterval', 'bi', InputOption::VALUE_OPTIONAL, "Burst interval number of normal message in between bursts.", 10),
                new InputOption('burstSize', 'bs', InputOption::VALUE_OPTIONAL, "Number of jobs in a burst.", 100),
                new InputOption('jobInterupt', 'ji', InputOption::VALUE_OPTIONAL, "Normal job interrupt in microseconds.", 200000),
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

        //normal message
        //job size in microseconds
        $normalJobSize = $input->getOption('normalJobSize');
        //TODO: change rand in stats_rand_gen_normal (install stats module with PECL)
        $jobNormal = array('user_id' => 'normal', 'image_path' => '/path/to/new/pic.png', 'size' => rand($normalJobSize/2,$normalJobSize));
        //burst job
        //job size in microseconds
        $burstJobSize = $input->getOption('burstJobSize');
        $jobBurst = array('user_id' => 'burst', 'image_path' => '/path/to/new/pic.png', 'size' => rand($burstJobSize/2, $burstJobSize));
        $jobCounter = 0;
        //burst interval
        $burstInterval = $input->getOption('burstInterval');
        //burst size
        $burstSize = $input->getOption('burstSize');
        //normal job timing in microseconds
        $jobInterupt = $input->getOption('jobInterupt');

        //connection with graphite
        $collector = $container->get('beberlei_metrics.collector.statsd');

        while($workloadSizeCounter < $workloadSize)
        {
            //job interupt
            usleep($jobInterupt);
            if($jobCounter == $burstInterval)
            {
                //add message burst
                for($i=0;$i<$burstSize;$i++)
                {
                    $container->get('old_sound_rabbit_mq.upload_picture_producer')->publish(serialize($jobBurst));
                    $collector->increment('alvi.jobs');
                    //send stats to graphite
                    $collector->flush();
                }
                //reset job counter
                $jobCounter = 0;
            }
            else
            {
                //send normal message
                $container->get('old_sound_rabbit_mq.upload_picture_producer')->publish(serialize($jobNormal));
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