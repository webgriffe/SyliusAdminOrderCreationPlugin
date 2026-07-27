<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusAdminOrderCreationPlugin\Repository;

use Webgriffe\SyliusAdminOrderCreationPlugin\Doctrine\ORM\CustomerRepositoryInterface;
use Webgriffe\SyliusAdminOrderCreationPlugin\Doctrine\ORM\CustomerRepositoryTrait;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\CustomerRepository as BaseCustomerRepository;

final class CustomerRepository extends BaseCustomerRepository implements CustomerRepositoryInterface
{
    use CustomerRepositoryTrait;
}
