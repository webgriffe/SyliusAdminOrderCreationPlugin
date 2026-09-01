# Agent Instructions for SyliusAdminOrderCreationPlugin

## What this is

A Sylius 2.x plugin that lets an Administrator create (or reorder) an Order on behalf of a Customer directly from the admin panel — choosing channel/locale/currency, adding items with custom price adjustments, picking shipping/payment, previewing before confirming, and optionally generating/sending a payment link.

- Plugin namespace: `Webgriffe\SyliusAdminOrderCreationPlugin`
- Test namespace: `Tests\Webgriffe\SyliusAdminOrderCreationPlugin`
- Requires PHP 8.2+, Sylius ^2.0, Symfony ^7.4

## Commands

### Linting & Static Analysis
```bash
vendor/bin/ecs check                                  # coding standard (fix: add --fix)
vendor/bin/phpstan analyse                             # level max, baseline in phpstan-baseline.neon
vendor/bin/psalm --no-cache                            # errorLevel 4, baseline in psalm-baseline.xml
```

### Tests
```bash
vendor/bin/phpspec run                                # unit specs (spec/)
vendor/bin/phpunit                                     # all PHPUnit testsuites
vendor/bin/phpunit --testsuite=unit                    # tests/Unit
vendor/bin/phpunit --testsuite=functional              # tests/Functional
vendor/bin/phpunit --testsuite=integration             # tests/Integration
vendor/bin/phpunit --testsuite=non-unit                # functional + integration
vendor/bin/behat --strict -vvv --no-interaction         # Behat acceptance suite
```

Or run everything as CI does: `composer suite` (ecs → phpstan → psalm → phpunit → phpspec → behat).

`vendor/bin/console` / `bin/console` are symlinks created by `bin/create_console_symlink.php`, which runs automatically post-install/post-update.

## Architecture

```
src/                          Plugin source, loaded via WebgriffeSyliusAdminOrderCreationExtension
  Controller/                 Order create/preview/customer-selection/ajax shipping-method actions
  Factory/                    Order factory
  Preparator/                 New-order preparation logic
  Provider/                   Shipping method / customer / payment token providers
  EventListener/              Order creation, payment-link creation
  ReorderProcessing/          Composite reorder processor + item/payment/shipment/data processors
  Sender/                     Order payment-link sender
  Doctrine/ORM/                Repository traits/interfaces the host app must wire in
  Form/                       Form types
  Migrations/                 Doctrine migrations
  DependencyInjection/        Bundle extension + configuration tree

config/
  config.yaml                  Main plugin config
  services.xml, services/      Service definitions
  ajax.yaml, routing.yaml      Routes

templates/                    Twig templates (flat `templates/` dir, Sylius 2 convention — not src/Resources/views)

tests/
  TestApplication/             Full Symfony app (sylius/test-application) used by PHPUnit and Behat
    config/bundles.php          Registers only this plugin bundle
    config/config.yaml          Imports plugin config + host-app overrides (repositories, etc.)
  Unit/, Integration/, Functional/   PHPUnit suites
  Behat/                        Contexts, pages, elements, feature files

spec/                          phpspec specs, mirrors src/ structure
```

## Key Conventions

### Language
All code must be in English — class names, method names, variable names, comments, log messages, and exception messages. Italian is only acceptable in user-facing content (Twig templates, translation files under `translations/`).

### PHP
- All PHP files: `declare(strict_types=1);` — no exceptions.
- Classes default to `final` unless extension is explicitly needed.
- Only add inline comments when the implementation logic is genuinely complex and requires explanation. Keep code self-documenting through clear naming.

### Static Analysis & Style
- PHPStan at level max (baseline in `phpstan-baseline.neon`); `src/DependencyInjection/Configuration.php` is excluded (crashes PHPStan).
- Psalm at error level 4 (baseline in `psalm-baseline.xml`).
- Coding standard: `sylius-labs/coding-standard` ECS ruleset applied to `src/`, `spec/`, `tests/Behat/`, and `ecs.php`.

### Tests
- **Unit logic** (mappers, factories, preparators, providers, processors with few dependencies): phpspec, under `spec/`, mirroring `src/` structure — this is the primary unit-testing tool in this plugin.
- **Integration/Functional** (`tests/Integration`, `tests/Functional`): `KernelTestCase`-based, boot the real `sylius/test-application` kernel — use for anything touching the container, Doctrine, or the HTTP layer.
- **Behat** (`tests/Behat`): admin-panel acceptance flows (order creation, reorder, payment link). Contexts under `tests/Behat/Context`, pages under `tests/Behat/Page`.

### Configuration & Services
- New services go in `config/services/` (or `config/services.xml` for the top-level list).
- `composer.lock` is gitignored — dependency versions float on every `composer install`. `api-platform/metadata`, `api-platform/symfony`, `symfony/config`, `symfony/property-info` and `symfony/type-info` are pinned to exact versions in `composer.json` to avoid a known incompatibility between newer `api-platform/symfony` and `sylius/sylius` 2.2.x routing — don't loosen these pins without re-verifying compatibility.
- After any change to the plugin's namespace, class map, or `composer.json` autoload section, run `composer dump-autoload` **inside the Docker container** too, not just locally — the container's autoload map is what the running app actually uses.

### Git
- This plugin is mid-migration from Sylius 1.x to Sylius 2.x (branch `sylius-2`). Infrastructure (composer deps, DI/config layout, state machine, test harness, Behat, CI) is migrated. The Twig/UI layer is not: templates still target removed Sylius 1.x paths (`@SyliusAdmin/layout.html.twig`, Semantic UI markup) and need rewriting against Sylius 2's Bootstrap/Tabler admin UI and Twig Hooks system for the Order Show page override.
