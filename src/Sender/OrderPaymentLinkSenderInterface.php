<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\Sender;

use Sylius\Component\Core\Model\OrderInterface;

interface OrderPaymentLinkSenderInterface
{
    public function sendPaymentLink(OrderInterface $order): void;
}
