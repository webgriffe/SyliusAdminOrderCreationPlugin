<?php

declare(strict_types=1);

namespace Webgriffe\SyliusAdminOrderCreationPlugin\Controller;

use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Webgriffe\SyliusAdminOrderCreationPlugin\Factory\OrderFactoryInterface;
use Webgriffe\SyliusAdminOrderCreationPlugin\Form\Type\NewOrderType;

final class OrderPreviewAction
{
    /** @var OrderFactoryInterface */
    private $orderFactory;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var OrderProcessorInterface */
    private $orderProcessor;

    /** @var Environment */
    private $twig;

    public function __construct(
        OrderFactoryInterface $orderFactory,
        FormFactoryInterface $formFactory,
        OrderProcessorInterface $orderProcessor,
        Environment $twig,
    ) {
        $this->orderFactory = $orderFactory;
        $this->formFactory = $formFactory;
        $this->orderProcessor = $orderProcessor;
        $this->twig = $twig;
    }

    public function __invoke(Request $request): Response
    {
        $customerId = $request->attributes->get('customerId');
        $channelCode = $request->attributes->get('channelCode');

        $order = $this->orderFactory->createForCustomerAndChannel($customerId, $channelCode);

        $form = $this->formFactory->create(NewOrderType::class, $order);
        $order = $form->handleRequest($request)->getData();
        $this->orderProcessor->process($order);

        return new Response($this->twig->render('@WebgriffeSyliusAdminOrderCreationPlugin/order/preview.html.twig', [
            'form' => $form->createView(),
        ]));
    }
}
