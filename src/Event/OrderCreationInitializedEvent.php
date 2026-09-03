<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\Event;

use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class OrderCreationInitializedEvent extends Event
{
    /** @var OrderInterface */
    private $order;

    public function __construct(OrderInterface $order)
    {
        $this->order = $order;
    }

    public function getOrder(): OrderInterface
    {
        return $this->order;
    }
}
