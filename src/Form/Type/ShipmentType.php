<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodChoiceType;
use Sylius\Component\Shipping\Model\ShippingSubjectInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ShipmentType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $methodOptions = [
            'required' => true,
            'label' => 'sylius.form.checkout.shipping_method',
        ];

        if ($options['subject'] !== null) {
            $methodOptions['subject'] = $options['subject'];
        }

        $builder->add('method', ShippingMethodChoiceType::class, $methodOptions);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('subject', null);
        $resolver->setAllowedTypes('subject', ['null', ShippingSubjectInterface::class]);
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix(): string
    {
        return 'sylius_admin_order_creation_new_order_shipment';
    }
}
