<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\Form\Type;

use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\AdminBundle\Form\Type\TranslatableAutocompleteType;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;

#[AsEntityAutocompleteField(
    alias: 'webgriffe_sylius_admin_order_creation_product_variant_in_channel',
    route: 'sylius_admin_entity_autocomplete',
)]
final class ProductVariantInChannelAutocompleteType extends AbstractType
{
    public function __construct(private readonly string $productVariantClass)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => $this->productVariantClass,
            'filter_query' => function (Options $options): ?callable {
                $channelCode = $options['extra_options']['channel_code'] ?? null;

                if ($channelCode === null) {
                    return null;
                }

                return function (QueryBuilder $queryBuilder, string $query, EntityRepository $repository) use ($channelCode): void {
                    $queryBuilder
                        ->innerJoin('entity.channelPricings', 'channelPricing')
                        ->andWhere('channelPricing.channelCode = :channelPricingChannelCode')
                        ->setParameter('channelPricingChannelCode', $channelCode)
                    ;
                };
            },
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'webgriffe_sylius_admin_order_creation_product_variant_in_channel_autocomplete';
    }

    public function getParent(): string
    {
        return TranslatableAutocompleteType::class;
    }
}
