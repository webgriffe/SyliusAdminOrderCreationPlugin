<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusAdminOrderCreationPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Repository\PaymentMethodRepositoryInterface;

final class PaymentContext implements Context
{
    public function __construct(
        private readonly SharedStorageInterface $sharedStorage,
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository,
        private readonly ExampleFactoryInterface $paymentMethodExampleFactory,
    ) {
    }

    /**
     * @Given /^the store has a payment method "([^"]+)" with a code "([^"]+)" and (.+) gateway$/
     */
    public function theStoreHasAPaymentMethodWithACodeAndGateway(
        string $paymentMethodName,
        string $paymentMethodCode,
        string $gatewayLabel,
    ): void {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->paymentMethodExampleFactory->create([
            'name' => $paymentMethodName,
            'code' => $paymentMethodCode,
            'gatewayName' => $gatewayLabel,
            'gatewayFactory' => StringInflector::nameToLowercaseCode($gatewayLabel),
            'enabled' => true,
            'channels' => $this->sharedStorage->has('channel') ? [$this->sharedStorage->get('channel')] : [],
        ]);

        $this->sharedStorage->set('payment_method', $paymentMethod);
        $this->paymentMethodRepository->add($paymentMethod);
    }
}
