<?php

namespace Ebizmarts\BrippoPayments\Controller\ExpressCheckout;

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Express as ExpressHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class RemoveCoupon extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $expressHelper;
    protected $checkoutSession;
    protected $storeManager;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param ExpressHelper $expressHelper
     * @param CheckoutSession $checkoutSession
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context                 $context,
        JsonFactory             $jsonFactory,
        Logger                  $logger,
        ExpressHelper           $expressHelper,
        CheckoutSession         $checkoutSession,
        StoreManagerInterface   $storeManager
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->expressHelper = $expressHelper;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $this->logger->log('Trying to remove coupon code...');
        $this->expressHelper->setParamsFromRequestBody($this->getRequest());
        $shippingAddress = $this->getRequest()->getParam('shippingAddress');

        try {
            $quote = $this->checkoutSession->getQuote();
            $scopeId = $this->storeManager->getStore()->getId();
            $currency = $this->expressHelper->getModalCurrencyFromQuote($quote, $scopeId);

            $quote->setCouponCode(null);

            if (!empty($shippingAddress)) {
                $this->expressHelper->setShippingAddressToQuoteFromPaymentRequestData(
                    $quote,
                    $shippingAddress,
                    $quote->getQuoteCurrencyCode()
                );
            } else {
                $quote->collectTotals()->save();
            }

            if (!empty($quote->getCouponCode())) {
                throw new LocalizedException(__('Unable to remove discount code.'));
            }

            $this->logger->log('Coupon code removed successfully.');

            $response = [
                'valid' => 1,
                'options' => $this->expressHelper->getCartOptions($quote, $currency, $scopeId)
            ];
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            $this->messageManager->addErrorMessage($ex->getMessage());
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
