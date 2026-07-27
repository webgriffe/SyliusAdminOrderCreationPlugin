<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\Provider;

use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface PaymentTokenProviderInterface
{
    public function getPaymentToken(PaymentInterface $payment): TokenInterface;
}
