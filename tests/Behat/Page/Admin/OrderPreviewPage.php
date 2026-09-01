<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusAdminOrderCreationPlugin\Behat\Page\Admin;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;

final class OrderPreviewPage extends SymfonyPage implements OrderPreviewPageInterface
{
    public function getRouteName(): string
    {
        return 'sylius_admin_order_creation_preview_order';
    }

    public function getTotal(): string
    {
        return trim($this->getDocument()->find('css', 'td#total')->getText());
    }

    public function getShippingTotal(): string
    {
        return trim($this->getDocument()->find('css', 'td#shipping-total')->getText());
    }

    public function hasProduct(string $productName): bool
    {
        return $this->getDocument()->has('css', sprintf('.sylius-product-name:contains("%s")', $productName));
    }

    public function hasPayment(string $paymentName): bool
    {
        return $this->getDocument()->has('css', sprintf('#sylius-payments .item:contains("%s")', $paymentName));
    }

    public function hasConfirmButton(): bool
    {
        return null !== $this->getDocument()->findButton('Confirm');
    }

    public function hasOrderDiscountValidationMessage(string $message): bool
    {
        $validationMessage = $this
            ->getDocument()
            ->find('css', '[data-test-order-discount] .invalid-feedback')
        ;

        return $validationMessage !== null && trim($validationMessage->getText()) === $message;
    }

    public function hasItemDiscountValidationMessage(string $productCode, string $message): bool
    {
        $row = $this->getDocument()->find('css', sprintf('[data-test-item]:contains("%s")', $productCode));

        if ($row === null) {
            return false;
        }

        $validationMessage = $row->find('css', '[data-test-item-discount] .invalid-feedback');

        return $validationMessage !== null && trim($validationMessage->getText()) === $message;
    }

    public function hasLocale(string $localeName): bool
    {
        $localeElement = $this->getDocument()->find('css', '#sylius-order-locale-code');

        return $localeElement !== null && strpos($localeElement->getText(), $localeName) !== false;
    }

    public function hasCurrency(string $currencyName): bool
    {
        $currencyElement = $this->getDocument()->find('css', '#sylius-order-currency');

        return $currencyElement !== null && strpos($currencyElement->getText(), $currencyName) !== false;
    }

    public function lowerOrderPriceBy(string $discount): void
    {
        $discountCard = $this->getDocument()->find('css', '[data-test-order-discount]');
        \assert($discountCard !== null);
        $discountCard->pressButton('Add discount');

        $this->getDocument()->waitFor(5, function () use ($discountCard) {
            return $discountCard->hasField('Order discount');
        });

        $discountCard->fillField('Order discount', $discount);
    }

    public function lowerItemWithProductPriceBy(string $productCode, string $discount): void
    {
        $row = $this->getDocument()->find('css', sprintf('[data-test-item]:contains("%s")', $productCode));
        \assert($row !== null);
        $row->pressButton('Add discount');

        $this->getDocument()->waitFor(5, function () use ($row) {
            return $row->hasField('Item discount');
        });

        $row->fillField('Item discount', $discount);
    }

    public function confirm(): void
    {
        $this->getDocument()->pressButton('Confirm');
    }

    public function goBack(): void
    {
        $this->getDocument()->pressButton('Back');
    }
}
