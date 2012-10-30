<?php

namespace Alvi\Bundle\ImageProcessor\ZookeeperBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for the extension.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder $builder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $rootNode = $builder->root('alvi_image_processor_zookeeper');
        $rootNode
            ->children()
                ->scalarNode('hosts')->cannotBeEmpty()->isRequired()->end()
            ->end();

        return $builder;
    }
}
