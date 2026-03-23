<?php

namespace Ebizmarts\BrippoPayments\Controller\Payments;

use Ebizmarts\BrippoPayments\Helper\ExpressCheckoutElement;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\PaymentMethods\PaymentMethod as PaymentMethodHelper;
use Ebizmarts\BrippoPayments\Helper\PlatformService\Analytics;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Analytic extends Action
{
    /** @var Logger */
    protected $logger;

    /** @var PaymentMethodHelper */
    protected $eceHelper;

    protected $analyticsService;
    protected $storeManager;

    public function __construct(
        Context                $context,
        Logger                 $logger,
        ExpressCheckoutElement $eceHelper,
        Analytics              $analyticsService,
        StoreManagerInterface  $storeManager
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->eceHelper = $eceHelper;
        $this->analyticsService = $analyticsService;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        try {
            $scopeId = $this->storeManager->getStore()->getId();
            $this->eceHelper->setParamsFromRequestBody($this->getRequest());
            $environment = $this->getRequest()->getParam('environment');
            $message = $this->getRequest()->getParam('message');
            if (empty($message)) {
                $message = "";
            }
            $eventType = $this->getRequest()->getParam('eventType');
            if (empty($eventType)) {
                $eventType = Analytics::EVENT_TYPE_PAYMENT_REQUEST;
            }

            $this->analyticsService->sendAnalytic(
                ScopeInterface::SCOPE_STORE,
                $scopeId,
                $eventType,
                $environment,
                $message
            );
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage(), Logger::SERVICE_API_LOG);
        }

        return $this->_response;
    }
}
