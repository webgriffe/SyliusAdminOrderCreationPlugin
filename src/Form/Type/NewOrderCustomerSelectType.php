<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\Form\Type;

use Sylius\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class NewOrderCustomerSelectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer', CustomerAutocompleteType::class, [
                'label' => 'sylius.ui.customer',
                'required' => false,
                'constraints' => [
                    new NotBlank(message: 'sylius_admin_order_creation.no_customer_selected'),
                ],
            ])
            ->add('channel', ChannelChoiceType::class, [
                'label' => 'sylius.ui.channel',
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'sylius_admin_order_creation_new_order_customer_select';
    }
}
