<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\Event;

use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class PaymentLinkGeneratedEvent extends Event
{
    /** @var PaymentInterface */
    private $payment;

    public function __construct(PaymentInterface $payment)
    {
        $this->payment = $payment;
    }

    public function getPayment(): PaymentInterface
    {
        return $this->payment;
    }
}
