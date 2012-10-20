<?php

namespace Alvi\Bundle\ImageProcessor\SchedulerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScheduleJobsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('alvi:image-processor:schedule')
            ->setDescription('Schedule incoming image process jobs.')
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
        $msg = array('user_id' => 1235, 'image_path' => '/path/to/new/pic.png');
        $this->getContainer()->get('old_sound_rabbit_mq.upload_picture_producer')->publish(serialize($msg));
        $output->writeln('Scheduled a <info>job</info> for client <comment>foo</comment>.');

    }
}
