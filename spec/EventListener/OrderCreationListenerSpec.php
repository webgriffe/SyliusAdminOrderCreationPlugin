<?php

declare(strict_types=1);

namespace spec\Sylius\AdminOrderCreationPlugin\EventListener;

use PhpSpec\ObjectBehavior;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class OrderCreationListenerSpec extends ObjectBehavior
{
    function let(
        OrderProcessorInterface $orderProcessor,
        StateMachineInterface $stateMachine,
    ) {
        $this->beConstructedWith($orderProcessor, $stateMachine);
    }

    function it_processes_order_before_creation(
        OrderProcessorInterface $orderProcessor,
        GenericEvent $event,
        OrderInterface $order,
    ) {
        $event->getSubject()->willReturn($order);

        $order->recalculateAdjustmentsTotal()->shouldBeCalled();
        $orderProcessor->process($order)->shouldBeCalled();

        $this->processOrderBeforeCreation($event);
    }

    function it_completes_order_before_creation(
        StateMachineInterface $stateMachine,
        GenericEvent $event,
        OrderInterface $order,
    ) {
        $event->getSubject()->willReturn($order);

        $stateMachine->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_ADDRESS)->shouldBeCalled();
        $stateMachine->can($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING)->willReturn(true);
        $stateMachine->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING)->shouldBeCalled();
        $stateMachine->can($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_SELECT_PAYMENT)->willReturn(true);
        $stateMachine->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_SELECT_PAYMENT)->shouldBeCalled();
        $stateMachine->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_COMPLETE)->shouldBeCalled();

        $this->completeOrderBeforeCreation($event);
    }

    function it_completes_order_without_payment_before_creation(
        StateMachineInterface $stateMachine,
        GenericEvent $event,
        OrderInterface $order,
    ) {
        $event->getSubject()->willReturn($order);

        $stateMachine->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_ADDRESS)->shouldBeCalled();
        $stateMachine->can($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING)->willReturn(true);
        $stateMachine->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING)->shouldBeCalled();
        $stateMachine->can($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_SELECT_PAYMENT)->willReturn(false);
        $stateMachine->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_SELECT_PAYMENT)->shouldNotBeCalled();
        $stateMachine->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_COMPLETE)->shouldBeCalled();

        $this->completeOrderBeforeCreation($event);
    }

    function it_completes_order_without_shipping_before_creation(
        StateMachineInterface $stateMachine,
        GenericEvent $event,
        OrderInterface $order,
    ) {
        $event->getSubject()->willReturn($order);

        $stateMachine->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_ADDRESS)->shouldBeCalled();
        $stateMachine->can($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING)->willReturn(false);
        $stateMachine->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING)->shouldNotBeCalled();
        $stateMachine->can($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_SELECT_PAYMENT)->willReturn(true);
        $stateMachine->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_SELECT_PAYMENT)->shouldBeCalled();
        $stateMachine->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_COMPLETE)->shouldBeCalled();

        $this->completeOrderBeforeCreation($event);
    }

    function it_throws_exception_if_event_subject_is_not_order(GenericEvent $event)
    {
        $event->getSubject()->willReturn('badObject', 'badObject');

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('processOrderBeforeCreation', [$event]);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('completeOrderBeforeCreation', [$event]);
    }
}
