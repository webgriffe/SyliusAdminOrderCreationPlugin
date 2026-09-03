<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusAdminOrderCreationPlugin\Integration\DependencyInjection;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Webgriffe\SyliusAdminOrderCreationPlugin\Factory\OrderFactoryInterface;
use Webmozart\Assert\Assert;

final class OrderFactoryServiceWiringTest extends KernelTestCase
{
    public function testOrderFactoryInterfaceIsWiredToTheOrderFactoryService(): void
    {
        self::bootKernel();
        Assert::notNull(self::$kernel);
        $container = self::$kernel->getContainer();

        $orderFactory = $container->get(OrderFactoryInterface::class);

        self::assertInstanceOf(OrderFactoryInterface::class, $orderFactory);
    }
}
