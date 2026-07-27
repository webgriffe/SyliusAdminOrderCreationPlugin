<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\Doctrine\ORM;

use Sylius\Component\Core\Repository\CustomerRepositoryInterface as BaseCustomerRepositoryInterface;

interface CustomerRepositoryInterface extends BaseCustomerRepositoryInterface
{
    public function findByEmailPart(string $email): array;
}
