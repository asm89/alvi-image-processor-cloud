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
                new InputOption('scalerpolicy', 'sp', InputOption::VALUE_OPTIONAL, "Scaler policy, options: time, constantsize, queuesize, queuerate", "pwn"),
                new InputOption('decisionInterval', 'dI', InputOption::VALUE_OPTIONAL, "The interval between policy decisions", 1)
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
            case 'queuesize':
                $policy = $container->get('alvi.image_processor.scaler.policy.QueueSizePolicy');
            break;
            case 'queuerate':
                $policy = $container->get('alvi.image_processor.scaler.policy.QueueRatePolicy');
            break;
            case 'pwn':
                $policy = $container->get('alvi.image_processor.scaler.policy.pwn_policy');
            break;
            default:
                $policy = $container->get('alvi.image_processor.scaler.policy.processfinishtimepolicy');
        }

        while (true) {
            $policy->policyDecision();

            sleep($decisionInterval);
        }
    }
}
