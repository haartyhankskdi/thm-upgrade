<?php

namespace Ebizmarts\BrippoPayments\Controller\PaymentElement;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents as BrippoPaymentIntentsApi;
use Ebizmarts\BrippoPayments\Helper\Data;
use Ebizmarts\BrippoPayments\Helper\PaymentElement;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Store\Model\StoreManagerInterface;

class Update extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $checkoutSession;
    protected $storeManager;
    protected $brippoApiPaymentIntents;
    protected $dataHelper;
    protected $paymentElementHelper;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param CheckoutSession $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param BrippoPaymentIntentsApi $brippoApiPaymentIntents
     * @param Data $dataHelper
     * @param PaymentElement $paymentElementHelper
     */
    public function __construct(
        Context                 $context,
        JsonFactory             $jsonFactory,
        Logger                  $logger,
        CheckoutSession         $checkoutSession,
        StoreManagerInterface   $storeManager,
        BrippoPaymentIntentsApi $brippoApiPaymentIntents,
        Data                    $dataHelper,
        PaymentElement          $paymentElementHelper
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->brippoApiPaymentIntents = $brippoApiPaymentIntents;
        $this->dataHelper = $dataHelper;
        $this->paymentElementHelper = $paymentElementHelper;
    }

    public function execute()
    {
        $this->paymentElementHelper->setParamsFromRequestBody($this->getRequest());

        $scopeId = $this->storeManager->getStore()->getId();
        $paymentIntentId = $this->getRequest()->getParam('paymentIntentId');

        try {
            $quote = $this->checkoutSession->getQuote();
            $currency = $this->paymentElementHelper->getPaymentIntentCurrencyFromQuote($quote, $scopeId);
            $grandTotal = $this->paymentElementHelper->getQuoteGrandTotal($quote, $scopeId);

            $this->brippoApiPaymentIntents->update(
                $paymentIntentId,
                $grandTotal,
                $currency,
                $this->dataHelper->isLiveMode($scopeId)
            );

            $response = [
                'valid' => 1
            ];
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            $response = [
                'valid' => 0,
                'message' => $ex->getMessage()
            ];
        }

        $resultJson = $this->jsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }
}
