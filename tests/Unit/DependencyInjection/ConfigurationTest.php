<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusAdminOrderCreationPlugin\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Webgriffe\SyliusAdminOrderCreationPlugin\DependencyInjection\Configuration;

final class ConfigurationTest extends TestCase
{
    public function testItDefaultsOfflineGatewayNamesToOffline(): void
    {
        $processedConfiguration = (new Processor())->processConfiguration(new Configuration(), []);

        self::assertSame(['offline'], $processedConfiguration['offline_gateway_names']);
    }

    public function testItAllowsConfiguringCustomOfflineGatewayNames(): void
    {
        $processedConfiguration = (new Processor())->processConfiguration(new Configuration(), [
            ['offline_gateway_names' => ['offline', 'bank_transfer']],
        ]);

        self::assertSame(['offline', 'bank_transfer'], $processedConfiguration['offline_gateway_names']);
    }

    public function testItDefaultsPaymentLinkGenerationToEnabled(): void
    {
        $processedConfiguration = (new Processor())->processConfiguration(new Configuration(), []);

        self::assertTrue($processedConfiguration['payment_link_generation_enabled']);
    }

    public function testItAllowsDisablingPaymentLinkGeneration(): void
    {
        $processedConfiguration = (new Processor())->processConfiguration(new Configuration(), [
            ['payment_link_generation_enabled' => false],
        ]);

        self::assertFalse($processedConfiguration['payment_link_generation_enabled']);
    }
}
