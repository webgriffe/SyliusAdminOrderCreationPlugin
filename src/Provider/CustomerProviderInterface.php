<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\Provider;

use Sylius\Component\Core\Model\CustomerInterface;

interface CustomerProviderInterface
{
    public function provideExistingCustomer(string $id): CustomerInterface;

    public function provideNewCustomer(string $email): CustomerInterface;
}
