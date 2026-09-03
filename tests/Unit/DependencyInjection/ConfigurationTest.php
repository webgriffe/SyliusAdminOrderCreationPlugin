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
}
