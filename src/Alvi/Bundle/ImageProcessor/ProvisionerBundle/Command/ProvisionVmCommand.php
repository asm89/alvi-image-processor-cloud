<?php

namespace Alvi\Bundle\ImageProcessor\ProvisionerBundle\Command;

use Alvi\Bundle\ImageProcessor\ProvisionerBundle\VirtualMachineConfiguration;

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
        $container = $this->getContainer();
        $vm = new VirtualMachineConfiguration($input->getArgument('type'), $input->getOption('memory'));

        $output->writeln(sprintf('Provisioning a <info>%s</info> VM with <info>%sMB</info> ram.', $vm->getType(), $vm->getMemory()));

        $vagrantProvisioner = $container->get('alvi.image_processor.provisioner.vagrant_provisioner');

        $vm = $vagrantProvisioner->provision($vm);

        $output->writeln(sprintf('Successfully provisioned the VM with ip <info>%s</info> and fqdn <info>%s</info>.', $vm->getIp(), $vm->getFqdn()));
    }
}
