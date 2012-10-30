<?php

namespace Alvi\Bundle\ImageProcessor\ScalerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

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
            ->setHelp(<<<EOT
The <info>%command.name%</info> command will scale the workers in the cloud.

  <info>php %command.full_name%</info>
EOT
            );
        }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $policy = $container->get('alvi.image_processor.scaler.policy.processfinishtimepolicy');

        while (true) {
            //wait 10 seconds and rescale the system.
            sleep(1);
            //choose a policy
            $policy->policyDecision();
        }
    }
}
