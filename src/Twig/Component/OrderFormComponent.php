<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\Twig\Component;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;
use Webgriffe\SyliusAdminOrderCreationPlugin\Factory\OrderFactoryInterface;
use Webgriffe\SyliusAdminOrderCreationPlugin\Form\Type\NewOrderType;

#[AsLiveComponent(
    name: 'webgriffe_sylius_admin_order_creation:order_form',
    template: '@WebgriffeSyliusAdminOrderCreationPlugin/order/create/_order_form_component.html.twig',
)]
class OrderFormComponent
{
    use LiveCollectionTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public string $customerId;

    #[LiveProp]
    public string $channelCode;

    public function __construct(
        private readonly OrderFactoryInterface $orderFactory,
        private readonly FormFactoryInterface $formFactory,
        private readonly OrderProcessorInterface $orderProcessor,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->formFactory->create(NewOrderType::class, $this->createOrder(), [
            'shipmentChoicesSubject' => $this->computeShipmentChoicesSubject(),
        ]);
    }

    private function createOrder(): OrderInterface
    {
        return $this->orderFactory->createForCustomerAndChannel($this->customerId, $this->channelCode);
    }

    /**
     * Builds a throwaway order from the current (live, not-yet-final) form values so that the
     * shipment's "method" field can be restricted to the shipping methods actually eligible for
     * the items/address entered so far, mirroring Sylius' own checkout behaviour.
     */
    private function computeShipmentChoicesSubject(): ?ShipmentInterface
    {
        if ($this->formValues === []) {
            return null;
        }

        try {
            $order = $this->createOrder();
            $this->formFactory->create(NewOrderType::class, $order)->submit($this->formValues);
            $this->orderProcessor->process($order);
        } catch (\Throwable) {
            return null;
        }

        $shipment = $order->getShipments()->first();

        return $shipment instanceof ShipmentInterface ? $shipment : null;
    }
}
