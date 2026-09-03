<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\EventListener;

use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webgriffe\SyliusAdminOrderCreationPlugin\Event\OrderCreatedByAdminEvent;
use Webmozart\Assert\Assert;

final class OrderCreationListener
{
    /** @var OrderProcessorInterface */
    private $orderProcessor;

    /** @var StateMachineInterface */
    private $stateMachine;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        OrderProcessorInterface $orderProcessor,
        StateMachineInterface $stateMachine,
        EventDispatcherInterface $eventDispatcher,
    ) {
        $this->orderProcessor = $orderProcessor;
        $this->stateMachine = $stateMachine;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function processOrderBeforeCreation(GenericEvent $event): void
    {
        $order = $event->getSubject();
        Assert::isInstanceOf($order, OrderInterface::class);

        $order->recalculateAdjustmentsTotal();
        $this->orderProcessor->process($order);
    }

    public function completeOrderBeforeCreation(GenericEvent $event): void
    {
        $order = $event->getSubject();
        Assert::isInstanceOf($order, OrderInterface::class);

        $this->stateMachine->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_ADDRESS);
        if ($this->stateMachine->can($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING)) {
            $this->stateMachine->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING);
        }
        if ($this->stateMachine->can($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_SELECT_PAYMENT)) {
            $this->stateMachine->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_SELECT_PAYMENT);
        }
        $this->stateMachine->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_COMPLETE);
    }

    public function dispatchOrderCreatedEvent(GenericEvent $event): void
    {
        $order = $event->getSubject();
        Assert::isInstanceOf($order, OrderInterface::class);

        $this->eventDispatcher->dispatch(new OrderCreatedByAdminEvent($order));
    }
}
