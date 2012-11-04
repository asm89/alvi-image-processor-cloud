<?php

namespace Alvi\Bundle\ImageProcessor\ProvisionerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provisions vms of given type.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class ProvisionVmCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('alvi:image-processor:provision-vm')
            ->setDescription('Provision a vm')
            ->setDefinition(array(
                new InputArgument('type', InputArgument::REQUIRED, "Type of virtual machine."),
                new InputOption('memory', 'm', InputOption::VALUE_OPTIONAL, "Amount of memory the VM can use.", 256),
                new InputOption('stop', null, InputOption::VALUE_NONE, 'Stop a vm.')
            ))
            ->setHelp(<<<EOT
The <info>%command.name%</info> command will provision a vm.

  <info>php %command.full_name%</info>
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');

        $manager = $this->getContainer()->get('alvi.image_processor.provisioner.manager');

        if ($input->getOption('stop')) {
            $output->writeln(sprintf('Stopping a <info>%s</info> VM.', $type));
            $manager->stop($type);
        } else {
            $output->writeln(sprintf('Starting a <info>%s</info> VM.', $type));
            $manager->start($type);
        }
    }
}
