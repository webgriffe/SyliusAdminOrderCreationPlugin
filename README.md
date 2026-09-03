<p align="center">
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>

<h1 align="center">Admin Order Creation Plugin</h1>

<p align="center"><a href="https://sylius.com/plugins/" target="_blank"><img src="https://sylius.com/assets/badge-official-sylius-plugin.png" width="200"></a></p>

<p align="center">This plugin allows to create an order in admin panel.</p>

![Screenshot showing the order creation page, Shipments&Payments section](docs/screenshot.png)

## Business value

So far it was up to the Customer to place an order using available product variants as well as payment and shipping
methods.

The whole process of placing an order is not that obvious, however. For some reason a Customer may feel a little bit
confused when a promotion is no longer available or shipping method is not eligible for given area. Here comes
Admin Order Creation Plugin.

Briefly speaking, it allows an Administrator to place or reorder an order in the name of a Customer. It helps them solve
even more of Customers' fundamental problems and equips an Administrator with basic tools making creating an 
order possible.

Admin Order Creation Plugin processes are strongly based on standard Order model taken from SyliusCoreBundle.
The only things that differ are order creation context and business requirements. Right now it is up to the Administrator
to provide a channel, locale and currency in which an Order is created. What's more, the Administrator is able to add
a discount for any item or the whole Order, which is, technically speaking, a new type of Sylius Adjustments.

After creating an Order via Admin panel, this new Order is listed like any other order placed via Sylius.

## Installation

Requires Sylius `~2.2.0` and PHP `^8.2`.

1. Require plugin with composer:

    ```bash
    composer require webgriffe/sylius-admin-order-creation-plugin
    ```

    > Remember to allow community recipes with `composer config extra.symfony.allow-contrib true` or during plugin installation process

2. Register the bundle in `config/bundles.php`:

    ```php
    Webgriffe\SyliusAdminOrderCreationPlugin\WebgriffeSyliusAdminOrderCreationPlugin::class => ['all' => true],
    ```

3. Import plugin configuration in `config/packages/sylius_admin_order_creation_plugin.yaml`:

    ```yaml
    imports:
        - { resource: "@WebgriffeSyliusAdminOrderCreationPlugin/config/config.yaml" }
    ```

4. Import plugin routes in `config/routes/sylius_admin_order_creation_plugin.yaml`:

    ```yaml
    sylius_admin_order_creation_plugin:
        resource: "@WebgriffeSyliusAdminOrderCreationPlugin/config/routing.yaml"
    ```

5. Copy Sylius templates overridden in plugin to your templates directory (e.g `templates/bundles/`):

    ```bash
    mkdir -p templates/bundles/SyliusAdminBundle/
    cp -R vendor/webgriffe/sylius-admin-order-creation-plugin/templates/bundles/SyliusAdminBundle/* templates/bundles/SyliusAdminBundle/
    ```

6. Override repositories

   1. Create repository classes
      ```bash
      mkdir src/Repository
      touch src/Repository/CustomerRepository.php
      touch src/Repository/ProductVariantRepository.php
      ```
   2. Paste the following content to the `src/Repository/CustomerRepository.php`:
      ```php
      <?php
    
      declare(strict_types=1);
    
      namespace App\Repository;
      
      use Webgriffe\SyliusAdminOrderCreationPlugin\Doctrine\ORM\CustomerRepositoryInterface;
      use Webgriffe\SyliusAdminOrderCreationPlugin\Doctrine\ORM\CustomerRepositoryTrait;
      use Sylius\Bundle\CoreBundle\Doctrine\ORM\CustomerRepository as BaseCustomerRepository;
      
      final class CustomerRepository extends BaseCustomerRepository implements CustomerRepositoryInterface
      {
          use CustomerRepositoryTrait;
      }
      ```
   3. Paste the following content to the `src/Repository/ProductVariantRepository.php`:
      ```php
      <?php
    
      declare(strict_types=1);
    
      namespace App\Repository;

      use Webgriffe\SyliusAdminOrderCreationPlugin\Doctrine\ORM\ProductVariantRepositoryInterface;
      use Webgriffe\SyliusAdminOrderCreationPlugin\Doctrine\ORM\ProductVariantRepositoryTrait;
      use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductVariantRepository as BaseProductVariantRepository;
      
      final class ProductVariantRepository extends BaseProductVariantRepository implements ProductVariantRepositoryInterface
      {
          use ProductVariantRepositoryTrait;
      }
      ```
   4. Configure repositories in `config/packages/_sylius.yaml`:
   ```diff
    sylius_customer:
        resources:
            customer:
                classes:
                    model: App\Entity\Customer\Customer
   +                repository: App\Repository\CustomerRepository
   
    sylius_product:
        resources:
            product_variant:
                classes:
                    model: App\Entity\Product\ProductVariant
   +                repository: App\Repository\ProductVariantRepository
   ```
      

## Extension points

### Configuration

The plugin exposes semantic configuration under `sylius_admin_order_creation_plugin`:

```yaml
sylius_admin_order_creation_plugin:
    # Gateway names for which no payment link is generated after an order is created from the admin panel.
    offline_gateway_names: ['offline']
    # Whether to generate (and optionally send) a payment link at all after an order is created from the admin panel.
    payment_link_generation_enabled: true
```

### Events

In addition to the generic Sylius core `sylius.order.pre_admin_create` / `sylius.order.post_admin_create` events the
plugin itself listens to, it dispatches its own typed events at the points a host application is most likely to
need a hook. Listen to any of them with a plain `#[AsEventListener]` instead of decorating or replacing the
plugin's own services:

- `Event\OrderCreationInitializedEvent`, dispatched by `OrderFactory` whenever an order is initialized for admin
  creation or reorder (carries the `OrderInterface`). Use it to guard/veto (e.g. throw an `AccessDeniedException`)
  or enrich the order before it's shown or further processed.
- `Event\OrderCreatedByAdminEvent`, dispatched right after an order is created from the admin panel (carries the
  created `OrderInterface`). Use it for side effects that only make sense once the order actually exists
  (notifications, audit logging, ...).
- `Event\PaymentLinkGeneratedEvent`, dispatched right after a payment link is generated for a payment (carries the
  `PaymentInterface`), independently of whether the "send by email" checkbox was ticked.

### Payment link generation

Payment link generation and sending is based on logic placed in the `PaymentLinkCreationListener` class. It can be
turned off entirely via the `payment_link_generation_enabled` configuration flag, or replaced altogether by
decorating/replacing the service for more advanced needs.

### Order Show templates (Twig Hooks)

Order Show template sections related to this plugin (discount rows, payment-link action, ...) are registered as
[Twig Hooks](https://docs.sylius.com/the-book/customization/twig-hooks) in `config/twig_hooks/order_show.yaml`.
Override or add your own hookable template at the same hook name (with a different priority) to customize them -
see that file for the exact hook names in use.

The order creation, preview and select-customer pages are not yet migrated to Twig Hooks and are still overridable
only the classic Symfony way, by placing a template at the same bundle-relative path under your own
`templates/bundles/WebgriffeSyliusAdminOrderCreationPlugin/` directory (see `templates/order/` in this repository
for the paths to override). This is part of the still-ongoing Sylius 2 UI migration mentioned below.

### Adjustments

The set of order/item adjustment types is not closed - adding a custom adjustment means defining a new constant on
your own adjustment-type class; `AdjustmentType`'s own constants (`ORDER_DISCOUNT_ADJUSTMENT`,
`ORDER_ITEM_DISCOUNT_ADJUSTMENT`) are not extensible themselves, since the class is `final`.

### Reorder processing

Significant part of Reorder Processing is inspired by official Sylius 
[Customer Reorder Plugin](https://github.com/Sylius/CustomerReorderPlugin/). In case of the need for more processors,
just add new class implementing `ReorderProcessor` interface, declare it in `reorder_processing.xml` file and match
it with a proper tag.

### Forms

Admin Order Creation process is based on Symfony Forms. To find out more about Symfony Forms extension possibilities, check out
[Symfony Docs](https://symfony.com/doc/current/form/create_form_type_extension.html).

### Factory

`Factory\OrderFactoryInterface` is aliased as a service, and routes resolve their order factory through that alias
rather than the concrete `OrderFactory` class, so a host application can decorate `OrderFactoryInterface` and have
its decorator picked up wherever the plugin creates an order (e.g. to attach the placing administrator to the
order, or to reuse an in-progress order from session storage).

## Development

### Docker

1. Copy `compose.override.dist.yml` to `compose.override.yml` and adjust it to your needs.

2. Start the containers:

    ```bash
    docker compose up -d
    ```

3. Install PHP dependencies and initialize the test application:

    ```bash
    docker compose exec php composer install
    docker compose exec php composer test-app-init
    ```

4. The test application is available at `http://localhost`.

### Running the test suite

```bash
docker compose exec php composer suite
```

runs ECS, PHPStan, Psalm, PHPSpec, PHPUnit and Behat in sequence (each can also be run individually via `composer ecs`,
`composer phpstan`, `composer psalm`, `composer phpspec`, `composer phpunit`, `composer behat`).

## Security issues

If you think that you have found a security issue, please do not use the issue tracker and do not post it publicly. 
Instead, all security issues must be sent to `security@sylius.com`.
