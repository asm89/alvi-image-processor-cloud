<?php

namespace Alvi\Bundle\ImageProcessor\ScalerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Vincent <vincentvanbeek@mac.com>
 */
class ScalerCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('alvi:image-processor:scaler')
            ->setDescription('Start and stop VM\'s.')
            ->setDefinition(array(
                new InputOption('scalerpolicy', 'sp', InputOption::VALUE_OPTIONAL, "Scaler policy, options: constantsize, moving-average-queue-size, queuesize, queuerate, time", "queuesize"),
                new InputOption('decisionInterval', 'dI', InputOption::VALUE_OPTIONAL, "The interval between policy decisions", 10)
                ))
            ->setHelp(<<<EOT
The <info>%command.name%</info> command will scale the workers in the cloud.

  <info>php %command.full_name%</info>
EOT
            );
        }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $decisionInterval = $input->getOption('decisionInterval');

        $container = $this->getContainer();
        switch ($input->getOption('scalerpolicy')) {
            case 'time':
                $policy = $container->get('alvi.image_processor.scaler.policy.processfinishtimepolicy');
            break;
            case 'constantsize':
                $policy = $container->get('alvi.image_processor.scaler.policy.constantsizepolicy');
            break;
            case 'moving-average-queue-size':
                $policy = $container->get('alvi.image_processor.scaler.policy.MovingAverageQueueSizePolicy');
            break;
            case 'queuerate':
                $policy = $container->get('alvi.image_processor.scaler.policy.QueueRatePolicy');
            break;
            case 'queuesize':
                $policy = $container->get('alvi.image_processor.scaler.policy.queue_size_policy');
            break;
            default:
                throw new \RuntimeException(sprintf("Unknown policy '%s'.", $input->getOption('scalerpolicy')));
        }

        while (true) {
            $policy->policyDecision();

            sleep($decisionInterval);
        }
    }
}
