<?php

namespace Alvi\Bundle\ImageProcessor\ProvisionerBundle\Deployer;

use Alvi\Bundle\ImageProcessor\ProvisionerBundle\DeployerInterface;
use Alvi\Bundle\ImageProcessor\ProvisionerBundle\Deployer\Messaged\Command\DestroyCommand;
use Alvi\Bundle\ImageProcessor\ProvisionerBundle\Deployer\Messaged\Command\ProvisionCommand;
use Alvi\Bundle\ImageProcessor\ProvisionerBundle\VirtualMachine;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

/**
 * Class for callback messages sent from the DeployListener.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class MessagedAsyncDeployer implements DeployerInterface
{
    private $producer;

    /**
     * @param Producer $producer
     */
    public function __construct(Producer $producer)
    {
        $this->producer = $producer;
    }

    /**
     * {@inheritDoc}
     */
    public function provision(VirtualMachine $vm)
    {
        $this->sendCommand(new ProvisionCommand($vm));
    }

    /**
     * {@inheritDoc}
     */
    public function destroy(VirtualMachine $vm)
    {
        $this->sendCommand(new DestroyCommand($vm));
    }

    /**
     * @param mixed $command
     */
    private function sendCommand($command)
    {
        $this->producer->publish(serialize($command));
    }
}
