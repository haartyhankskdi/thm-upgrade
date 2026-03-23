<?php

namespace Ebizmarts\BrippoPayments\Controller\Payments;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\Service;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Helper\PaymentElement as PaymentElementHelper;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;

class Status extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $checkoutSession;
    protected $storeManager;
    protected $scopeConfig;
    protected $dataHelper;
    protected $stripeHelper;
    protected $paymentElementHelper;
    protected $json;

    /** @var PaymentIntents */
    protected $paymentIntentsApi;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param DataHelper $dataHelper
     * @param Stripe $stripeHelper
     * @param PaymentElementHelper $paymentElementHelper
     * @param PaymentIntents $paymentIntentsApi
     * @param Json $json
     */
    public function __construct(
        Context                             $context,
        JsonFactory                         $jsonFactory,
        Logger                              $logger,
        StoreManagerInterface               $storeManager,
        ScopeConfigInterface                $scopeConfig,
        DataHelper                          $dataHelper,
        Stripe                              $stripeHelper,
        PaymentElementHelper                $paymentElementHelper,
        PaymentIntents                      $paymentIntentsApi,
        Json                                $json
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->dataHelper = $dataHelper;
        $this->stripeHelper = $stripeHelper;
        $this->paymentElementHelper = $paymentElementHelper;
        $this->paymentIntentsApi = $paymentIntentsApi;
        $this->json = $json;
    }

    public function execute()
    {
        try {
            $paymentIntentId = $this->getRequest()->getParam('paymentIntentId');

            if (empty($paymentIntentId)) {
                $this->setParamsFromRequestBody($this->getRequest());
                $paymentIntentId = $this->getRequest()->getParam('paymentIntentId');
            }
            if (empty($paymentIntentId)) {
                throw new LocalizedException(__("Invalid payment id"));
            }

            $scopeId = $this->storeManager->getStore()->getId();
            $paymentIntentData = $this->paymentIntentsApi->get(
                $paymentIntentId,
                $this->dataHelper->isLiveMode($scopeId)
            );
            $status = $paymentIntentData[Service::PARAM_PI_STATUS];

            $response = [
                'valid' => 1,
                'status' => $status
            ];
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            $response = [
                'valid' => 0,
                'status' => null,
                'message' => $ex->getMessage()
            ];
        }

        $resultJson = $this->jsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }

    /**
     * @param $request
     * @return void
     */
    private function setParamsFromRequestBody($request): void
    {
        if (!empty($request->getContent())) {
            try {
                $contentType = $request->getHeader('Content-Type');
                
                // Handle URL-encoded form data
                if ($contentType && strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
                    parse_str($request->getContent(), $params);
                    if (is_array($params) && !empty($params)) {
                        $request->setParams($params);
                    }
                } else {
                    // Handle JSON data
                    $jsonVars = $this->json->unserialize($request->getContent());
                    if (is_array($jsonVars) && !empty($jsonVars)) {
                        $request->setParams($jsonVars);
                    }
                }
            } catch (Exception $e) {
                $this->logger->log("Failed to unserialize request body: ". $e->getMessage());
                $this->logger->log("CT: ". $request->getHeader('Content-Type'));
                $this->logger->log("Method: ". $request->getMethod());
                $this->logger->log($request->getContent());
            }
        }
    }
}
