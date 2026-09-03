<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sylius_admin_order_creation_plugin');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('offline_gateway_names')
                    ->info('Payment gateway names for which no payment link is generated after an order is created from the admin panel.')
                    ->scalarPrototype()->end()
                    ->defaultValue(['offline'])
                ->end()
                ->booleanNode('payment_link_generation_enabled')
                    ->info('Whether to generate (and optionally send) a payment link after an order is created from the admin panel.')
                    ->defaultTrue()
        ;

        return $treeBuilder;
    }
}
