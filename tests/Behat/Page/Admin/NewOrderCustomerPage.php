<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusAdminOrderCreationPlugin\Behat\Page\Admin;

use Behat\Mink\Session;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;
use Sylius\Behat\Service\Helper\AutocompleteHelperInterface;
use Symfony\Component\Routing\RouterInterface;

final class NewOrderCustomerPage extends SymfonyPage implements NewOrderCustomerPageInterface
{
    public function __construct(
        Session $session,
        $parameters,
        RouterInterface $router,
        private readonly AutocompleteHelperInterface $autocompleteHelper,
    ) {
        parent::__construct($session, $parameters, $router);
    }

    public function getRouteName(): string
    {
        return 'sylius_admin_order_creation_select_order_customer';
    }

    public function selectCustomer(string $customerEmail): void
    {
        $this->autocompleteHelper->selectByName(
            $this->getDriver(),
            $this->getElement('customer_autocomplete')->getXpath(),
            $customerEmail,
        );
    }

    public function next(): void
    {
        $this->getDocument()->pressButton('Next');
    }

    public function createCustomer(string $email): void
    {
        $this->getDocument()->fillField('New customer email', $email);
        $this->getDocument()->pressButton('Create new');
    }

    public function selectChannel(string $channelName): void
    {
        foreach ($this->getDocument()->findAll('css', 'select[name$="[channel]"]') as $select) {
            $select->selectOption($channelName);
        }
    }

    public function hasCustomerEmailValidationMessage(string $message): bool
    {
        foreach ($this->getDocument()->findAll('css', '.invalid-feedback') as $validationMessage) {
            if (trim($validationMessage->getText()) === $message) {
                return true;
            }
        }

        return false;
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'customer_autocomplete' => 'select[name$="[customer]"]',
        ]);
    }
}
