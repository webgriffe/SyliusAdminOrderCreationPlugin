<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusAdminOrderCreationPlugin\Behat\Element\Admin;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use Sylius\Behat\Service\Helper\AutocompleteHelperInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Tests\Webgriffe\SyliusAdminOrderCreationPlugin\Behat\Element\Element;

class OrderCreateFormElement extends Element implements OrderCreateFormElementInterface
{
    public function __construct(
        Session $session,
        $parameters,
        private readonly AutocompleteHelperInterface $autocompleteHelper,
    ) {
        parent::__construct($session, $parameters);
    }

    public function addProduct(string $productVariantDescriptor): void
    {
        $item = $this->addItemAndWaitForIt();

        $this->autocompleteHelper->selectByName(
            $this->getDriver(),
            $this->getVariantSelect($item)->getXpath(),
            $productVariantDescriptor,
        );
        $this->waitForComponentIdle();
    }

    public function addMultipleProducts(string $productVariantDescriptor, int $quantity): void
    {
        $item = $this->addItemAndWaitForIt();

        $this->autocompleteHelper->selectByName(
            $this->getDriver(),
            $this->getVariantSelect($item)->getXpath(),
            $productVariantDescriptor,
        );
        $this->waitForComponentIdle();
        $item->fillField('Quantity', (string) $quantity);
        $this->waitForComponentIdle();
    }

    public function removeProduct(string $productVariantDescriptor): void
    {
        $item = $this->getItemWithProductSelected($productVariantDescriptor);
        $item->pressButton('Delete');
        $this->waitForComponentIdle();
    }

    public function areProductsVisible(): bool
    {
        $this->clickOnTab('items');
        $item = $this->addItemAndWaitForIt();

        $results = $this->autocompleteHelper->search(
            $this->getDriver(),
            $this->getVariantSelect($item)->getXpath(),
            'a',
        );

        return [] !== $results;
    }

    public function specifyShippingAddress(AddressInterface $address): void
    {
        $this->clickOnTab('addresses');

        $this->fillAddressData(
            $this->getDocument()->find('css', 'div[id*="shippingAddress"]'),
            $address,
        );
    }

    public function specifyBillingAddress(AddressInterface $address): void
    {
        $this->clickOnTab('addresses');

        $this->fillAddressData(
            $this->getDocument()->find('css', 'div[id*="billingAddress"]'),
            $address,
        );
    }

    public function getAvailableShippingMethods(): array
    {
        $shipmentRow = $this->addShipmentRowAndWaitForIt();

        $shippingMethods = $shipmentRow->findAll('css', 'select[name$="[method]"] option');

        return array_map(static fn (NodeElement $option): string => $option->getText(), $shippingMethods);
    }

    public function moveToShippingAndPaymentsSection(): void
    {
        $this->clickOnTab('shipping-payment');
    }

    public function selectShippingMethod(string $shippingMethodName): void
    {
        $shipmentRow = $this->addShipmentRowAndWaitForIt();
        $shipmentRow->selectFieldOption('Shipping Method', $shippingMethodName);
    }

    public function changeShippingMethod(string $shippingMethodName): void
    {
        $this->clickOnTab('shipping-payment');
        $this->waitForComponentIdle();

        $shipmentRow = $this->findLast('[data-test-shipment-row]');
        $shipmentRow->selectFieldOption('Shipping Method', $shippingMethodName);
    }

    public function selectPaymentMethod(string $paymentMethodName): void
    {
        $paymentRow = $this->addPaymentRowAndWaitForIt();
        $paymentRow->selectFieldOption('Payment Method', $paymentMethodName);
    }

    public function changePaymentMethod(string $paymentMethodName): void
    {
        $this->clickOnTab('shipping-payment');
        $this->waitForComponentIdle();

        $paymentRow = $this->findLast('[data-test-payment-row]');
        $paymentRow->selectFieldOption('Payment Method', $paymentMethodName);
    }

    public function specifyQuantity(string $productVariantDescriptor, int $quantity): void
    {
        $item = $this->getItemWithProductSelected($productVariantDescriptor);

        $item->fillField('Quantity', (string) $quantity);
    }

    public function placeOrder(): void
    {
        $this->getDocument()->pressButton('Order preview');
    }

    public function selectLocale(string $localeName): void
    {
        $this->clickOnTab('locale');

        $this->getDocument()->selectFieldOption('Locale', $localeName);
    }

    public function selectCurrency(string $currencyName): void
    {
        $this->clickOnTab('locale');

        $this->getDocument()->selectFieldOption('Currency', $currencyName);
    }

    public function getShippingMethodsValidationMessage(): string
    {
        return $this
            ->getDocument()
            ->find('css', '[data-test-shipping-methods-requirement]')
            ->getText()
        ;
    }

    public function isAddPaymentButtonVisible(): bool
    {
        $this->clickOnTab('shipping-payment');

        $addPaymentButton = $this->getDocument()->findButton('Add payment');

        return $addPaymentButton !== null && $addPaymentButton->isVisible();
    }

    public function hasValidationErrors(): bool
    {
        return $this->getDocument()->has('css', '.invalid-feedback');
    }

    public function isDisplayed(): bool
    {
        return $this->getDocument()->findButton('Order preview') !== null;
    }

    private function fillAddressData(NodeElement $addressForm, AddressInterface $address): void
    {
        $countryCode = $address->getCountryCode();
        \assert($countryCode !== null);

        $addressForm->fillField('First name', $address->getFirstName());
        $addressForm->fillField('Last name', $address->getLastName());
        $addressForm->fillField('Street', $address->getStreet());
        $addressForm->selectFieldOption('Country', $countryCode);
        $addressForm->fillField('City', $address->getCity());
        $addressForm->fillField('Postcode', $address->getPostcode());
    }

    private function addItemAndWaitForIt(): NodeElement
    {
        $this->clickOnTab('items');
        $this->waitForComponentIdle();

        $itemsCount = $this->countItems();
        $this->getDocument()->pressButton('Add item');

        return $this->waitForLast('[data-test-item-row]', $itemsCount);
    }

    private function addShipmentRowAndWaitForIt(): NodeElement
    {
        $this->clickOnTab('shipping-payment');
        $this->waitForComponentIdle();

        $shipmentsCount = $this->countShipments();

        if (0 === $shipmentsCount) {
            $this->getDocument()->pressButton('Add shipment');

            return $this->waitForLast('[data-test-shipment-row]', $shipmentsCount);
        }

        $this->waitForComponentIdle();

        return $this->findLast('[data-test-shipment-row]');
    }

    private function addPaymentRowAndWaitForIt(): NodeElement
    {
        $this->clickOnTab('shipping-payment');
        $this->waitForComponentIdle();

        $paymentsCount = $this->countPayments();
        $this->getDocument()->pressButton('Add payment');

        return $this->waitForLast('[data-test-payment-row]', $paymentsCount);
    }

    private function waitForLast(string $cssSelector, int $previousCount): NodeElement
    {
        $result = $this->getDocument()->waitFor(15, function () use ($cssSelector, $previousCount) {
            $elements = $this->getDocument()->findAll('css', $cssSelector);

            return count($elements) > $previousCount ? end($elements) : null;
        });

        \assert($result instanceof NodeElement);

        return $result;
    }

    private function countItems(): int
    {
        return count($this->getDocument()->findAll('css', '[data-test-item-row]'));
    }

    private function countShipments(): int
    {
        return count($this->getDocument()->findAll('css', '[data-test-shipment-row]'));
    }

    private function countPayments(): int
    {
        return count($this->getDocument()->findAll('css', '[data-test-payment-row]'));
    }

    private function waitForComponentIdle(): void
    {
        // Live Component debounces model updates (150ms by default) before the
        // "busy" attribute appears, so a check right after a field change can
        // race ahead of a request that hasn't started yet.
        $this->getSession()->wait(300);

        $this->getDocument()->waitFor(15, function () {
            return $this->getDocument()->find('css', '[busy]') === null;
        });
    }

    private function getItemWithProductSelected(string $productVariantDescriptor): NodeElement
    {
        $this->waitForComponentIdle();

        foreach ($this->getDocument()->findAll('css', '[data-test-item-row]') as $item) {
            $selectedOption = $this->getVariantSelect($item)->find('css', 'option[selected]');

            if ($selectedOption !== null && str_contains($selectedOption->getText(), $productVariantDescriptor)) {
                return $item;
            }
        }

        throw new \InvalidArgumentException(sprintf('There is no item with product with descriptor "%s" selected', $productVariantDescriptor));
    }

    private function findLast(string $cssSelector): NodeElement
    {
        $elements = $this->getDocument()->findAll('css', $cssSelector);
        \assert([] !== $elements);

        return end($elements);
    }

    private function getVariantSelect(NodeElement $item): NodeElement
    {
        $select = $item->find('css', 'select[name$="[variant]"]');
        \assert($select !== null);

        return $select;
    }

    private function clickOnTab(string $tabName): void
    {
        $tab = $this->getDocument()->find('css', sprintf('[data-test-tab="%s"]', $tabName));

        if ($tab->hasClass('active')) {
            return;
        }

        $tab->click();

        $this->getDocument()->waitFor(5, function () use ($tabName) {
            return $this
                ->getDocument()
                ->find('css', sprintf('[data-test-tab="%s"]', $tabName))
                ->hasClass('active')
            ;
        });
    }
}
