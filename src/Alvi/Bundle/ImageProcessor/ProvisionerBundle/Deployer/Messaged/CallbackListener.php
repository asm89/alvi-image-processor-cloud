<?php

namespace Alvi\Bundle\ImageProcessor\ProvisionerBundle\Deployer\Messaged;

use Alvi\Bundle\ImageProcessor\ProvisionerBundle\Deployer\Messaged\Command\SetStateCommand;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Alvi\Bundle\ImageProcessor\ProvisionerBundle\VirtualMachineManager;

/**
 * Class for callback messages sent from the DeployListener.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class CallbackListener implements ConsumerInterface
{
    private $manager;

    /**
     * @param VirtualMachineManager $manager
     */
    public function __construct(VirtualMachineManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(AMQPMessage $msg)
    {
        $command = unserialize($msg->body);

        if ($command instanceof SetStateCommand) {
            $this->manager->setMachineState($command->getVirtualMachine(), $command->getState());
        } else {
            // unknown command
            // todo: log?
        }
    }
}
