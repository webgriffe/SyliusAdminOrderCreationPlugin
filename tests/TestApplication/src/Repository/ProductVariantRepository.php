<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusAdminOrderCreationPlugin\Repository;

use Webgriffe\SyliusAdminOrderCreationPlugin\Doctrine\ORM\ProductVariantRepositoryInterface;
use Webgriffe\SyliusAdminOrderCreationPlugin\Doctrine\ORM\ProductVariantRepositoryTrait;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductVariantRepository as BaseProductVariantRepository;

final class ProductVariantRepository extends BaseProductVariantRepository implements ProductVariantRepositoryInterface
{
    use ProductVariantRepositoryTrait;
}
