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
            ->scalarNode('client_id')->cannotBeEmpty()->isRequired()->end()
            ->scalarNode('email_signature')->isRequired()->cannotBeEmpty()->end()
            ->booleanNode('prorate')->defaultFalse()->cannotBeEmpty()->end()
            ->booleanNode('hooks_enabled')->defaultFalse()->cannotBeEmpty()->end()
                ->arrayNode('redirect_routes')
                    ->children()
                    ->scalarNode('customer_new')->defaultValue('clab_stripe_default_route')->cannotBeEmpty()->end()
                    ->scalarNode('customer_update')->defaultValue('clab_stripe_default_route')->cannotBeEmpty()->end()
                    ->scalarNode('customer_disable')->defaultValue('clab_stripe_default_route')->cannotBeEmpty()->end()
                    ->scalarNode('subscription_update')->defaultValue('clab_stripe_default_route')->cannotBeEmpty()->end()
                    ->scalarNode('account_confirm')->defaultValue('clab_stripe_default_route')->cannotBeEmpty()->end()
                    ->scalarNode('account_disconnect')->defaultValue('clab_stripe_default_route')->cannotBeEmpty()->end()
                    ->end()
                ->end()
        ->end();

        return $treeBuilder;
    }
}
