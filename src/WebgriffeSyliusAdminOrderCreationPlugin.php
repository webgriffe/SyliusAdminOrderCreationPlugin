<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin;

use Webgriffe\SyliusAdminOrderCreationPlugin\DependencyInjection\Compiler\RegisterReorderProcessorsPass;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

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
}
