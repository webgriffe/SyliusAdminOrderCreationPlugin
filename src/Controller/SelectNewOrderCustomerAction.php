<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\Controller;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Webgriffe\SyliusAdminOrderCreationPlugin\Form\Type\NewOrderCustomerCreateType;
use Webgriffe\SyliusAdminOrderCreationPlugin\Form\Type\NewOrderCustomerSelectType;

final class SelectNewOrderCustomerAction
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly Environment $twig,
        private readonly UrlGeneratorInterface $router,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $selectCustomerForm = $this->formFactory->create(NewOrderCustomerSelectType::class);
        $createCustomerForm = $this->formFactory->create(NewOrderCustomerCreateType::class);

        if ($request->query->has($selectCustomerForm->getName())) {
            $selectCustomerForm->handleRequest($request);

            if ($selectCustomerForm->isSubmitted() && $selectCustomerForm->isValid()) {
                /** @var CustomerInterface $customer */
                $customer = $selectCustomerForm->get('customer')->getData();
                /** @var ChannelInterface $channel */
                $channel = $selectCustomerForm->get('channel')->getData();

                return new RedirectResponse($this->router->generate('sylius_admin_order_creation_order_create', [
                    'customerId' => $customer->getId(),
                    'channelCode' => $channel->getCode(),
                ]));
            }
        }

        if ($request->query->has($createCustomerForm->getName())) {
            $createCustomerForm->handleRequest($request);

            if ($createCustomerForm->isSubmitted() && $createCustomerForm->isValid()) {
                /** @var ChannelInterface $channel */
                $channel = $createCustomerForm->get('channel')->getData();

                return new RedirectResponse($this->router->generate('sylius_admin_order_creation_customer_create', [
                    'customerEmail' => $createCustomerForm->get('customerEmail')->getData(),
                    'channelCode' => $channel->getCode(),
                ]));
            }
        }

        return new Response($this->twig->render('@WebgriffeSyliusAdminOrderCreationPlugin/order/select_customer.html.twig', [
            'selectCustomerForm' => $selectCustomerForm->createView(),
            'createCustomerForm' => $createCustomerForm->createView(),
        ]));
    }
}
