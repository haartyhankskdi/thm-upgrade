<?php

namespace Ebizmarts\BrippoPayments\Controller\Payments;

use Ebizmarts\BrippoPayments\Helper\ExpressCheckoutElement;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Ebizmarts\BrippoPayments\Helper\Logger;

class Log extends Action
{
    protected $logger;
    protected $eceHelper;

    public function __construct(
        Context                $context,
        Logger                 $logger,
        ExpressCheckoutElement $eceHelper
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->eceHelper = $eceHelper;
    }

    public function execute()
    {
        $this->eceHelper->setParamsFromRequestBody($this->getRequest());
        $message = $this->getRequest()->getParam('errorMessage');
        $step = $this->getRequest()->getParam('step');

        if (!empty($message)) {
            $this->logger->log('PaymentMethod error while ' . $step . ':');

            if (is_array($message)) {
                $message = implode(',', $message);
            }
            $this->logger->log($message);
        }

        return $this->_response;
    }
}
