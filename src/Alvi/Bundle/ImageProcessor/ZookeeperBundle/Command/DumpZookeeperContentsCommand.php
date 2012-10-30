<?php

namespace Alvi\Bundle\ImageProcessor\ZookeeperBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for dumping the contents of a zookeeper installation.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class DumpZookeeperContentsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('alvi:image-processor:dump-zookeeper-contents')
            ->setDescription('Dump the contents of zookeeper.')
                ->setHelp(<<<EOT
The <info>%command.name%</info> command can be used to inspect the contents of a zookeeper cluster.

  <info>php %command.full_name%</info>
EOT
            );
        }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $zookeeper = $this->getContainer()->get('alvi.image_processor.zookeeper');

        $this->outputContents($zookeeper, $output, '/');
    }

    private function outputContents($zookeeper, OutputInterface $output, $path, $node = '', $indent = '')
    {
        $zooPath = $this->createSubPath($path, $node);
        $value = $zookeeper->get($zooPath);

        $output->write(sprintf($indent . '<info>%s</info>: %s', '/' . $node, null === $value ? "NULL\n" : $this->catchedVardump($value)));

        $children = $zookeeper->getChildren($zooPath);
        foreach ($children as $child) {
            $this->outputContents($zookeeper, $output, $zooPath, $child, $indent . '  ');
        }
    }

    private function createSubPath($path, $sub)
    {
        if ('/' === $path) {
            return '/' . $sub;
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
