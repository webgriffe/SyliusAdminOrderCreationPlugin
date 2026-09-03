<?php

declare(strict_types=1);

namespace spec\Webgriffe\SyliusAdminOrderCreationPlugin\Factory;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Sylius\Component\Currency\Model\CurrencyInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webgriffe\SyliusAdminOrderCreationPlugin\Event\OrderCreationInitializedEvent;
use Webgriffe\SyliusAdminOrderCreationPlugin\Factory\OrderFactoryInterface;
use Webgriffe\SyliusAdminOrderCreationPlugin\ReorderProcessing\ReorderProcessor;

final class OrderFactorySpec extends ObjectBehavior
{
    function let(
        FactoryInterface $baseOrderFactory,
        CustomerRepositoryInterface $customerRepository,
        ChannelRepositoryInterface $channelRepository,
        ReorderProcessor $reorderProcessor,
        EventDispatcherInterface $eventDispatcher,
    ) {
        $this->beConstructedWith(
            $baseOrderFactory,
            $customerRepository,
            $channelRepository,
            $reorderProcessor,
            $eventDispatcher,
        );
    }

    function it_implements_order_factory_interface(): void
    {
        $this->shouldImplement(OrderFactoryInterface::class);
    }

    function it_delegates_creating_new_order(FactoryInterface $baseOrderFactory, OrderInterface $order): void
    {
        $baseOrderFactory->createNew()->willReturn($order);

        $this->createNew()->shouldReturn($order);
    }

    function it_creates_order_for_customer_with_default_channel_locale_and_currency(
        FactoryInterface $baseOrderFactory,
        CustomerRepositoryInterface $customerRepository,
        ChannelRepositoryInterface $channelRepository,
        EventDispatcherInterface $eventDispatcher,
        OrderInterface $order,
        CustomerInterface $customer,
        ChannelInterface $channel,
        CurrencyInterface $currency,
        LocaleInterface $locale,
    ): void {
        $baseOrderFactory->createNew()->willReturn($order);

        $customerRepository->find('1')->willReturn($customer);
        $channelRepository->findOneByCode('WEB-US')->willReturn($channel);

        $channel->getBaseCurrency()->willReturn($currency);
        $currency->getCode()->willReturn('USD');

        $channel->getDefaultLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');

        $order->setCustomer($customer)->shouldBeCalled();
        $order->setChannel($channel)->shouldBeCalled();
        $order->setCurrencyCode('USD')->shouldBeCalled();
        $order->setLocaleCode('en_US')->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::that(function (OrderCreationInitializedEvent $event) use ($order) {
            return $event->getOrder() === $order->getWrappedObject();
        }))->shouldBeCalled();

        $this
            ->createForCustomerAndChannel('1', 'WEB-US')
            ->shouldReturn($order)
        ;
    }

    function it_throws_an_exception_if_the_customer_does_not_exist(
        FactoryInterface $baseOrderFactory,
        CustomerRepositoryInterface $customerRepository,
    ): void {
        $customerRepository->find('1')->willReturn(null);

        $baseOrderFactory->createNew()->shouldNotBeCalled();

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('createForCustomerAndChannel', ['1', 'WEB-US'])
        ;
    }

    function it_throws_an_exception_if_there_is_no_default_currency(
        FactoryInterface $baseOrderFactory,
        CustomerRepositoryInterface $customerRepository,
        ChannelRepositoryInterface $channelRepository,
        OrderInterface $order,
        CustomerInterface $customer,
        ChannelInterface $channel,
    ): void {
        $baseOrderFactory->createNew()->willReturn($order);

        $customerRepository->find('1')->willReturn($customer);
        $channelRepository->findOneByCode('WEB-US')->willReturn($channel);

        $channel->getBaseCurrency()->willReturn(null);

        $order->setCustomer($customer)->shouldBeCalled();
        $order->setChannel($channel)->shouldBeCalled();
        $order->setCurrencyCode(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('createForCustomerAndChannel', ['1', 'WEB-US'])
        ;
    }

    function it_throws_an_exception_if_there_is_no_default_locale(
        FactoryInterface $baseOrderFactory,
        CustomerRepositoryInterface $customerRepository,
        ChannelRepositoryInterface $channelRepository,
        OrderInterface $order,
        CustomerInterface $customer,
        ChannelInterface $channel,
        CurrencyInterface $currency,
    ): void {
        $baseOrderFactory->createNew()->willReturn($order);

        $customerRepository->find('1')->willReturn($customer);
        $channelRepository->findOneByCode('WEB-US')->willReturn($channel);

        $channel->getBaseCurrency()->willReturn($currency);
        $currency->getCode()->willReturn('USD');

        $channel->getDefaultLocale()->willReturn(null);

        $order->setCustomer($customer)->shouldBeCalled();
        $order->setChannel($channel)->shouldBeCalled();
        $order->setCurrencyCode('USD')->shouldBeCalled();
        $order->setLocaleCode(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('createForCustomerAndChannel', ['1', 'WEB-US'])
        ;
    }

    function it_creates_reorder_from_an_existing_order(
        FactoryInterface $baseOrderFactory,
        ReorderProcessor $reorderProcessor,
        EventDispatcherInterface $eventDispatcher,
        OrderInterface $order,
        OrderInterface $reorder,
    ): void {
        $baseOrderFactory->createNew()->willReturn($reorder);

        $reorderProcessor->process($order, $reorder)->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::that(function (OrderCreationInitializedEvent $event) use ($reorder) {
            return $event->getOrder() === $reorder->getWrappedObject();
        }))->shouldBeCalled();

        $this->createFromExistingOrder($order);
    }
}
