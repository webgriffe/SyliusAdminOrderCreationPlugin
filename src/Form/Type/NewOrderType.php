<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\Form\Type;

use Sylius\Bundle\AddressingBundle\Form\Type\AddressType;
use Sylius\Bundle\PromotionBundle\Form\Type\PromotionCouponToCodeType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Shipping\Model\ShippingSubjectInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

final class NewOrderType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('promotionCoupon', PromotionCouponToCodeType::class, [
                'by_reference' => false,
                'label' => 'sylius.form.cart.coupon',
                'required' => false,
            ])
            ->add('shippingAddress', AddressType::class, [
                'label' => 'sylius.ui.shipping_address',
            ])
            ->add('billingAddress', AddressType::class, [
                'label' => 'sylius.ui.billing_address',
                'required' => false,
            ])
            ->add('payments', LiveCollectionType::class, [
                'entry_type' => PaymentType::class,
                'label' => 'sylius.ui.payments',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('shipments', LiveCollectionType::class, [
                'entry_type' => ShipmentType::class,
                'entry_options' => [
                    'subject' => $options['shipmentChoicesSubject'],
                ],
                'label' => 'sylius.ui.shipments',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                /** @var OrderInterface $order */
                $order = $event->getData();

                /** @var ChannelInterface $channel */
                $channel = $order->getChannel();

                $event
                    ->getForm()
                    ->add('items', LiveCollectionType::class, [
                        'label' => false,
                        'entry_type' => OrderItemType::class,
                        'entry_options' => [
                            'currency' => $order->getCurrencyCode(),
                            'channelCode' => $channel->getCode(),
                        ],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'by_reference' => false,
                    ])
                    ->add('adjustments', LiveCollectionType::class, [
                        'label' => false,
                        'entry_type' => AdjustmentType::class,
                        'entry_options' => [
                            'label' => 'sylius_admin_order_creation.ui.order_discount',
                            'currency' => $order->getCurrencyCode(),
                            'type' => AdjustmentType::ORDER_DISCOUNT_ADJUSTMENT,
                        ],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'by_reference' => false,
                        'button_add_options' => [
                            'label' => 'sylius_admin_order_creation.ui.add_discount',
                        ],
                    ])
                    ->add('localeCode', LocaleCodeChoiceType::class, [
                        'label' => 'sylius.ui.locale',
                        'choices' => $channel->getLocales(),
                        'empty_data' => $order->getLocaleCode(),
                    ])
                    ->add('currencyCode', CurrencyCodeChoiceType::class, [
                        'label' => 'sylius.ui.currency',
                        'choices' => $channel->getCurrencies(),
                        'empty_data' => $order->getCurrencyCode(),
                    ])
                ;
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
                $orderData = $event->getData();

                if ($this->isShippingAddressComplete($orderData) && $this->isBillingAddressEmpty($orderData)) {
                    $orderData['billingAddress'] = $orderData['shippingAddress'];

                    $event->setData($orderData);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('shipmentChoicesSubject', null);
        $resolver->setAllowedTypes('shipmentChoicesSubject', ['null', ShippingSubjectInterface::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_order_creation_new_order';
    }

    private function isBillingAddressEmpty(array $orderData): bool
    {
        if (!isset($orderData['billingAddress'])) {
            return true;
        }

        return
            $orderData['billingAddress']['firstName'] === '' &&
            $orderData['billingAddress']['lastName'] === '' &&
            $orderData['billingAddress']['street'] === '' &&
            $orderData['billingAddress']['countryCode'] === '' &&
            $orderData['billingAddress']['city'] === '' &&
            $orderData['billingAddress']['postcode'] === ''
        ;
    }

    /**
     * The order creation page re-renders live as the admin types (via the order-form Live Component), so
     * this form's PRE_SUBMIT listener runs on every keystroke-triggered re-render, not just on the final
     * submit. Only copying the shipping address into an empty billing address once shipping is itself
     * fully filled in prevents a half-typed shipping address from being copied over field-by-field, which
     * would otherwise permanently block the rest of the copy (billing would no longer read as "empty").
     */
    private function isShippingAddressComplete(array $orderData): bool
    {
        if (!isset($orderData['shippingAddress'])) {
            return false;
        }

        foreach (['firstName', 'lastName', 'street', 'countryCode', 'city', 'postcode'] as $field) {
            if (($orderData['shippingAddress'][$field] ?? '') === '') {
                return false;
            }
        }

        return true;
    }
}
