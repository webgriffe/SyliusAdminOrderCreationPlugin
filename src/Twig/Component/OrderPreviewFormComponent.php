<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\Twig\Component;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent(
    name: 'webgriffe_sylius_admin_order_creation:order_preview_form',
    template: '@WebgriffeSyliusAdminOrderCreationPlugin/order/preview/_order_preview_form_component.html.twig',
)]
final class OrderPreviewFormComponent extends OrderFormComponent
{
}
