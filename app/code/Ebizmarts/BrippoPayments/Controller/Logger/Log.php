<?php

namespace Ebizmarts\BrippoPayments\Controller\Logger;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\PaymentMethods\PaymentMethod as PaymentMethodHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Log extends Action
{
    protected $logger;

    /** @var PaymentMethodHelper */
    protected $paymentMethodHelper;

    public function __construct(
        Context                $context,
        Logger                 $logger,
        PaymentMethodHelper         $paymentMethodHelper
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->paymentMethodHelper = $paymentMethodHelper;
    }

    public function execute()
    {
        $this->paymentMethodHelper->setParamsFromRequestBody($this->getRequest());

        $message = $this->getRequest()->getParam('message');
        $data = $this->getRequest()->getParam('data');

        if ($message) {
            $this->logger->log($message);
        }

        if ($data) {
            // phpcs:disable
            $this->logger->log(print_r($data, true));
            // phpcs:enable
        }

        return $this->_response;
    }
}
