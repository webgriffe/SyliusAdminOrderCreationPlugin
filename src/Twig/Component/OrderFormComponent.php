<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\Twig\Component;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Shipping\Resolver\ShippingMethodsResolverInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PreReRender;
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
        private readonly ShippingMethodsResolverInterface $shippingMethodsResolver,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->formFactory->create(NewOrderType::class, $this->createOrder(), [
            'shipmentChoicesSubject' => $this->computeShipmentChoicesSubject(),
        ]);
    }

    /**
     * Live Component actions (add/remove an item, a discount, a shipment...) only re-submit the
     * raw form values onto a freshly instantiated order - nothing in Live Component itself
     * recalculates derived data (unit prices, shipping cost, shipment/unit associations). Without
     * this, every such action would render the order as if it had just been created empty: all
     * prices at $0, shipments dropped, etc. This runs after Live Component's own form submission
     * (which happens at the default #[PreReRender] priority of 0), never on the initial render.
     */
    #[PreReRender(priority: -10)]
    public function reprocessOrder(): void
    {
        $order = $this->getForm()->getData();

        if (!$order instanceof OrderInterface) {
            return;
        }

        try {
            $this->orderProcessor->process($order);
        } catch (\Throwable) {
            // Items freshly added on the create page have no variant selected yet, which the
            // pricing processor can't handle - leave the order as submitted and let it settle
            // once the user picks a variant and this hook runs again.
        }
    }

    private function createOrder(): OrderInterface
    {
        return $this->orderFactory->createForCustomerAndChannel($this->customerId, $this->channelCode);
    }

    /**
     * Builds a throwaway order from the current (live, not-yet-final) form values so that the
     * shipment's "method" field can be restricted to the shipping methods actually eligible for
     * the items/address entered so far, mirroring Sylius' own checkout behaviour.
     *
     * The eligibility restriction is only ever meant to guide a *new* selection. Never let it
     * invalidate a method the admin already picked: zone matching on this throwaway, not-yet-fully
     * submitted order can be momentarily narrower than on the final order (e.g. while the address
     * form is only partially filled in across requests), and passing a subject whose eligible-method
     * list excludes the already-chosen method makes Symfony's ChoiceType treat that submitted value
     * as invalid - which silently drops the entire shipment from the collection instead of just
     * rejecting the method field.
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

        if (!$shipment instanceof ShipmentInterface) {
            return null;
        }

        if (
            !$this->shippingMethodsResolver->supports($shipment) ||
            !in_array($shipment->getMethod(), $this->shippingMethodsResolver->getSupportedMethods($shipment), true)
        ) {
            return null;
        }

        return $shipment;
    }
}
