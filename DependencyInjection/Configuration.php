<?php

namespace Clab\StripeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('clab_stripe', 'array')
        ->children()
            ->scalarNode('secret_key')->cannotBeEmpty()->isRequired()->end()
            ->scalarNode('publishable_key')->cannotBeEmpty()->isRequired()->end()
        ->end();

        return $treeBuilder;
    }
}
