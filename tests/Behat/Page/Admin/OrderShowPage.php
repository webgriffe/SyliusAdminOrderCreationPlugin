<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusAdminOrderCreationPlugin\Behat\Page\Admin;

use Sylius\Behat\Page\Admin\Order\ShowPage;

final class OrderShowPage extends ShowPage implements OrderShowPageInterface
{
    public function hasPaymentLink(): bool
    {
        $lastPayment = $this->getElement('payments')->find('css', '[data-test-payment]:last-child');

        if (null === $lastPayment) {
            return false;
        }

        return null !== $lastPayment->find('css', '[data-test-pay-via-payment-link]');
    }

    public function hasNoPaymentBlock(): bool
    {
        return null !== $this->getDocument()->find('css', $this->getDefinedElements()['no-payments']);
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'no-payments' => '[data-test-no-payments]',
        ]);
    }
}
