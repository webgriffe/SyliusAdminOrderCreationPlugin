<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\DependencyInjection\Compiler;

use Sylius\Bundle\ResourceBundle\DependencyInjection\Compiler\PrioritizedCompositeServicePass;

final class RegisterReorderProcessorsPass extends PrioritizedCompositeServicePass
{
    public function __construct()
    {
        parent::__construct(
            'Webgriffe\SyliusAdminOrderCreationPlugin\ReorderProcessing\CompositeReorderProcessor',
            'Webgriffe\SyliusAdminOrderCreationPlugin\ReorderProcessing\CompositeReorderProcessor',
            'sylius_admin_order_creation.reorder_processor',
            'addProcessor',
        );
    }
}
