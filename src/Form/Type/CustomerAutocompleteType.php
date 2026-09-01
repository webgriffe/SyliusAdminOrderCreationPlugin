<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField(
    alias: 'webgriffe_sylius_admin_order_creation_customer',
    route: 'sylius_admin_entity_autocomplete',
)]
final class CustomerAutocompleteType extends AbstractType
{
    public function __construct(private readonly string $customerClass)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => $this->customerClass,
            'choice_label' => 'email',
            'searchable_fields' => ['email'],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'webgriffe_sylius_admin_order_creation_customer_autocomplete';
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
