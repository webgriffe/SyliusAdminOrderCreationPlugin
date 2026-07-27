<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\ReorderProcessing;

use Sylius\Component\Core\Model\OrderInterface;

interface ReorderProcessor
{
    public function process(OrderInterface $order, OrderInterface $reorder): void;
}
