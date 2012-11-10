<?php

namespace Alvi\Bundle\ImageProcessor\ZookeeperBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\DialogHelper;

/**
 * Command for clearing the contents of a zookeeper installation.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class ClearZookeeperContentsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('alvi:image-processor:zookeeper:clear')
            ->setDescription('Clear the contents of zookeeper.')
                ->setHelp(<<<EOT
The <info>%command.name%</info> command can be used to clear all the contents of a zookeeper cluster.

  <info>php %command.full_name%</info>
EOT
            );
        }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = new DialogHelper();

        $clear = $dialog->askConfirmation($output, "<question>Are you sure you want to delete all contents from zookeeper?</question> [y/N] ", false);

        if (!$clear) {
            $output->writeln('<info>Will not delete anything, would have deleted:<info>');
        }

        $zookeeper = $this->getContainer()->get('alvi.image_processor.zookeeper');

        $children = $zookeeper->getChildren('/');

        foreach ($children as $child) {
            if ($child === 'zookeeper') {
                continue;
            }

            $this->deleteContents(!$clear, $zookeeper, $output, '/' . $child);
        }
    }

    private function deleteContents($dryRun, $zookeeper, OutputInterface $output, $path, $node = '')
    {
        $zooPath = $this->createSubPath($path, $node);

        $value = $zookeeper->get($zooPath);

        $children = $zookeeper->getChildren($zooPath);
        foreach ($children as $child) {
            $this->deleteContents($dryRun, $zookeeper, $output, $zooPath, $child);
        }

        $output->writeln('Deleting ' . $zooPath);

        if (!$dryRun) {
            $zookeeper->delete($zooPath);
        }
    }

    private function createSubPath($path, $sub)
    {
        if ('/' === $path) {
            return '/' . $sub;
        }
        if('' === $sub) {
            return $path;
        }
        return $path . '/' . $sub;
    }

    private function catchedVardump($var)
    {
        ob_start();
        var_dump($var);
        return ob_get_clean();
    }
}
