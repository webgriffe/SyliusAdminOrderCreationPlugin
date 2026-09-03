<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Webgriffe\SyliusAdminOrderCreationPlugin\DependencyInjection\Compiler\RegisterReorderProcessorsPass;
use Webgriffe\SyliusAdminOrderCreationPlugin\DependencyInjection\SyliusAdminOrderCreationExtension;

final class WebgriffeSyliusAdminOrderCreationPlugin extends Bundle
{
    use SyliusPluginTrait;

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RegisterReorderProcessorsPass());
    }

    #[\Override]
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new SyliusAdminOrderCreationExtension();
    }
}
