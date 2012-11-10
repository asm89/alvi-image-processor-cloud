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
                new InputArgument('workloadFilepath', InputArgument::REQUIRED, "Path to workload file to play"),
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

        $path = $input->getArgument('workloadFilepath');

        if (!is_file($path)) {
            throw new \RuntimeException(sprintf("Specified workload '%s' is not a file.", $path));
        }

        $collector = $container->get('beberlei_metrics.collector.statsd');
        $rabbitMQ = $container->get('old_sound_rabbit_mq.upload_picture_producer');
        $workload = $this->readWorkloadFromFile($path);
        foreach($workload as $job) {
            if ($job['jobInterupt'] != 0) {
                usleep($job['jobInterupt']);
            }

            $job['submitTime'] = microtime(true);

            $rabbitMQ->publish(serialize($job));

            $collector->increment('alvi.jobs');
            $collector->flush();
        }
    }

    /**
     * @param $path
     *
     * @return array with jobs
     */
    private function readWorkloadFromFile($path) {
        $file = fopen($path, 'r');

        $workload = array();
        while($line = fgets($file)) {
            $workload[] = json_decode($line, true);
        }

        return $workload;
    }
}
