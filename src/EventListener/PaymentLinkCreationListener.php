<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\EventListener;

use Doctrine\Persistence\ObjectManager;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Webgriffe\SyliusAdminOrderCreationPlugin\Form\Type\NewOrderType;
use Webgriffe\SyliusAdminOrderCreationPlugin\Provider\PaymentTokenProviderInterface;
use Webgriffe\SyliusAdminOrderCreationPlugin\Sender\OrderPaymentLinkSenderInterface;
use Webmozart\Assert\Assert;

final class PaymentLinkCreationListener
{
    /** @var PaymentTokenProviderInterface */
    private $paymentTokenProvider;

    /** @var ObjectManager */
    private $orderManager;

    /** @var OrderPaymentLinkSenderInterface */
    private $orderPaymentLinkSender;

    /** @var RequestStack */
    private $requestStack;

    /** @var list<string> */
    private $offlineGatewayNames;

    /** @var bool */
    private $enabled;

    /**
     * @param list<string> $offlineGatewayNames
     */
    public function __construct(
        PaymentTokenProviderInterface $paymentTokenProvider,
        ObjectManager $orderManager,
        OrderPaymentLinkSenderInterface $orderPaymentLinkSender,
        RequestStack $requestStack,
        array $offlineGatewayNames,
        bool $enabled,
    ) {
        $this->paymentTokenProvider = $paymentTokenProvider;
        $this->orderManager = $orderManager;
        $this->orderPaymentLinkSender = $orderPaymentLinkSender;
        $this->requestStack = $requestStack;
        $this->offlineGatewayNames = $offlineGatewayNames;
        $this->enabled = $enabled;
    }

    public function setPaymentLink(GenericEvent $event): void
    {
        if (!$this->enabled) {
            return;
        }

        /** @var OrderInterface $order */
        $order = $event->getSubject();
        Assert::isInstanceOf($order, OrderInterface::class);

        $payment = $order->getLastPayment(PaymentInterface::STATE_NEW);
        if (null === $payment) {
            return;
        }

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();
        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();

        if (\in_array($gatewayConfig->getGatewayName(), $this->offlineGatewayNames, true)) {
            return;
        }

        $token = $this->paymentTokenProvider->getPaymentToken($payment);
        $payment->setDetails(['payment-link' => $token->getAfterUrl()]);

        if ($this->shouldSendPaymentLinkEmail()) {
            $this->orderPaymentLinkSender->sendPaymentLink($order);
        }

        $this->orderManager->flush();
    }

    private function shouldSendPaymentLinkEmail(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return false;
        }

        $formData = $request->request->all(NewOrderType::BLOCK_PREFIX);

        return (bool) ($formData['sendPaymentLinkEmail'] ?? false);
    }
}
