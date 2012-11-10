<?php

namespace Alvi\Bundle\ImageProcessor\WorkerBundle\Command;

use OldSound\RabbitMqBundle\RabbitMq\Consumer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Allow workers to consume messages from arbitrary queues.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class WorkerCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('alvi:image-processor:worker')
            ->setDescription('Starts a worker.')
            ->setDefinition(array(
                new InputArgument('queue-name', InputArgument::REQUIRED, "Name of the queue to consume from."),
            ))
                ->setHelp(<<<EOT
The <info>%command.name%</info> command will start a worker.

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

        $consumer = new Consumer($container->get('old_sound_rabbit_mq.connection.default'));

        $consumer->setExchangeOptions(array('name' => $input->getArgument('queue-name'), 'type' => 'direct', 'passive' => false, 'durable' => true, 'auto_delete' => false, 'internal' => false, 'nowait' => false, 'arguments' => NULL, 'ticket' => NULL));
        $consumer->setQueueOptions(array('name' => $input->getArgument('queue-name'), 'passive' => false, 'durable' => true, 'exclusive' => false, 'auto_delete' => false, 'nowait' => false, 'arguments' => NULL, 'ticket' => NULL));
        $consumer->setCallback(array($container->get('alvi.image_processor.worker'), 'execute'));
        $consumer->setQosOptions(0, 1, 0);

        $consumer->consume(0);
    }
}
