<?php

namespace Ebizmarts\BrippoPayments\Controller\ExpressCheckout;

use Ebizmarts\BrippoPayments\Helper\Express as ExpressHelper;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;

class AddShippingMethod extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $dataHelper;
    protected $checkoutSession;
    protected $expressHelper;
    protected $quoteFactory;
    protected $storeManager;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param DataHelper $dataHelper
     * @param CheckoutSession $checkoutSession
     * @param ExpressHelper $expressHelper
     * @param QuoteFactory $quoteFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context                             $context,
        JsonFactory                         $jsonFactory,
        Logger                              $logger,
        DataHelper                          $dataHelper,
        CheckoutSession                     $checkoutSession,
        ExpressHelper                       $expressHelper,
        QuoteFactory                        $quoteFactory,
        StoreManagerInterface               $storeManager
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->expressHelper = $expressHelper;
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $this->expressHelper->setParamsFromRequestBody($this->getRequest());
        $shippingOption = $this->getRequest()->getParam('shippingOption');

        try {
            $quote = $this->checkoutSession->getQuote();
            $scopeId = $this->storeManager->getStore()->getId();
            $currency = $this->expressHelper->getModalCurrencyFromQuote($quote, $scopeId);

            $shippingOptionsToSend = $this->expressHelper->getShippingOptions($quote, $currency);
            if (empty($shippingOptionsToSend)) {
                throw new LocalizedException(__('No shipping options available for your address.'));
            }

            if (!$this->expressHelper->isShippingMethodAvailable($shippingOptionsToSend, $shippingOption['id'])) {
                throw new LocalizedException(__('Shipping option not available for your address.'));
            }

            $this->expressHelper->setShippingMethodToQuote($quote, $shippingOption['id']);
            $shippingOptionsToSend = $this->expressHelper->getShippingOptions($quote, $currency);
            $cartTotalOptions = $this->expressHelper->getCartOptions($quote, $currency, $scopeId);

            $response = [
                'valid' => 1,
                'updateDetails' => array_merge(
                    [
                        'status' => 'success',
                        'shippingOptions' => $shippingOptionsToSend
                    ],
                    $cartTotalOptions
                )
            ];
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            $response = [
                'valid' => 0,
                'message' => $ex->getMessage(),
                'updateDetails' => [
                    'status' => 'fail'
                ]
            ];
        }

        $resultJson = $this->jsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }
}