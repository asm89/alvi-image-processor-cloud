<?php

namespace Alvi\Bundle\ImageProcessor\ProvisionerBundle\Deployer\Messaged;

use Alvi\Bundle\ImageProcessor\ProvisionerBundle\Deployer\Messaged\Command\ProvisionCommand;
use Alvi\Bundle\ImageProcessor\ProvisionerBundle\Deployer\Messaged\Command\DestroyCommand;
use Alvi\Bundle\ImageProcessor\ProvisionerBundle\Deployer\Messaged\Command\SetStateCommand;
use Alvi\Bundle\ImageProcessor\ProvisionerBundle\ProvisionerInterface;
use Alvi\Bundle\ImageProcessor\ProvisionerBundle\VirtualMachine;
use Beberlei\Metrics\Collector\Collector;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class responsible for the actual deployment and destroying of VMs.
 *
 * Consumes messages which represent commands.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class DeployListener implements ConsumerInterface
{
    private $collector;
    private $producer;
    private $provisioner;

    /**
     * @param ProvisionerInterface $provisioner
     * @param Producer             $producer
     * @param Collector            $collector
     */
    public function __construct(ProvisionerInterface $provisioner, Producer $producer, Collector $collector)
    {
        $this->provisioner = $provisioner;
        $this->producer    = $producer;
        $this->collector   = $collector;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(AMQPMessage $msg)
    {
        $command = unserialize($msg->body);

        $this->collector->increment('alvi.deployer.commands_started');
        $this->collector->flush();

        $start = microtime(true);

        if ($command instanceof ProvisionCommand) {
            $this->provision($command);

            $this->collector->timing('alvi.deployer.vm_provision_time', microtime(true) - $start);

        } elseif ($command instanceof DestroyCommand) {
            $this->destroy($command);

            $this->collector->timing('alvi.deployer.vm_destroy_time', microtime(true) - $start);

        } else {
            // unknown command
            // todo: log?
        }

        $this->collector->increment('alvi.deployer.commands_finished');
        $this->collector->flush();
    }

    /**
     * @param ProvisionCommand $command
     */
    public function provision(ProvisionCommand $command)
    {
        $vm = $command->getVirtualMachine();

        // todo: log?
        //$output->writeln(sprintf('Provisioning a <info>%s</info> VM with <info>%sMB</info> ram.', $vm->getType(), $vm->getMemory()));

        $vm = $this->provisioner->provision($vm);

        if ($vm->isBooted()) {
            // todo: log?
            //$output->writeln(sprintf('Provisioning a <info>%s</info> VM with <info>%sMB</info> ram.', $vm->getType(), $vm->getMemory()));

            $this->sendCommand(new SetStateCommand($vm, VirtualMachine::STATE_RUNNING));
        } else {
            // todo: log?
            //$output->writeln('<error>Unable to boot the VM.</error>');

            $this->sendCommand(new SetStateCommand($vm, VirtualMachine::STATE_FAILED));
        }
    }

    /**
     * @param DestroyCommand $command
     */
    public function destroy(DestroyCommand $command)
    {
        $vm = $command->getVirtualMachine();

        $this->provisioner->destroy($vm);

        $this->sendCommand(new SetStateCommand($vm, VirtualMachine::STATE_DESTROYED));
    }

    /**
     * Serializes and sends a command.
     *
     * @param mixed $command
     */
    private function sendCommand($command)
    {
        $this->producer->publish(serialize($command));
    }
}
