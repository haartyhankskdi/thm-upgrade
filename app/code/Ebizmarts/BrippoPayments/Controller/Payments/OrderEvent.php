<?php

namespace Ebizmarts\BrippoPayments\Controller\Payments;

use Ebizmarts\BrippoPayments\Helper\ExpressCheckoutElement;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\PaymentMethods\PaymentMethod as PaymentMethodHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderEvent extends Action
{
    /** @var Logger */
    protected $logger;

    /** @var PaymentMethodHelper */
    protected $eceHelper;

    /** @var CheckoutSession */
    protected $checkoutSession;

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    public function __construct(
        Context                $context,
        Logger                 $logger,
        ExpressCheckoutElement $eceHelper,
        CheckoutSession        $checkoutSession,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->eceHelper = $eceHelper;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {

        $this->eceHelper->setParamsFromRequestBody($this->getRequest());
        $orderId = $this->getRequest()->getParam('orderId');
        $message = $this->getRequest()->getParam('eventMessage');
        $order = $this->orderRepository->get($orderId);

        if (!empty($order)) {
            $this->logger->logOrderEvent(
                $order,
                $message
            );
        }

        return $this->_response;
    }
}
