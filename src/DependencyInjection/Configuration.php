<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        return new TreeBuilder('sylius_admin_order_creation_plugin');
    }
}
