<?php

namespace Ebizmarts\BrippoPayments\Controller\ExpressCheckout;

use Ebizmarts\BrippoPayments\Model\Config\Source\ExpressLocation;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Express as ExpressHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class AddCoupon extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $expressHelper;
    protected $storeManager;
    protected $checkoutSession;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param StoreManagerInterface $storeManager
     * @param ExpressHelper $expressHelper
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Context                     $context,
        JsonFactory                 $jsonFactory,
        Logger                      $logger,
        StoreManagerInterface       $storeManager,
        ExpressHelper               $expressHelper,
        CheckoutSession             $checkoutSession
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->expressHelper = $expressHelper;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
    }

    public function execute()
    {
        $this->expressHelper->setParamsFromRequestBody($this->getRequest());
        $code = $this->getRequest()->getParam('code');
        $shippingAddress = $this->getRequest()->getParam('shippingAddress');
        $definedSource = $this->getRequest()->getParam('definedSource');
        $params = [];

        $this->logger->log('Trying to apply coupon code ' . $code . '...');
        try {
            $scopeId = $this->storeManager->getStore()->getId();
            $quote = $this->checkoutSession->getQuote();
            $currency = $this->expressHelper->getModalCurrencyFromQuote($quote, $scopeId);

            if (!empty($definedSource) && $definedSource['source'] === ExpressLocation::PRODUCT_PAGE) {
                // @codingStandardsIgnoreStart
                parse_str($this->getRequest()->getParam('request'), $params);
                // @codingStandardsIgnoreEnd
                $quote = $this->expressHelper->setUpProductPageQuote(
                    $quote,
                    $params,
                    $scopeId
                );
            }

            $quote->setCouponCode($code);

            if (!empty($shippingAddress)) {
                $this->expressHelper->setShippingAddressToQuoteFromPaymentRequestData(
                    $quote,
                    $shippingAddress,
                    $currency
                );
            } else {
                $quote->collectTotals()->save();
            }

            if ($quote->getCouponCode() != $code) {
                throw new LocalizedException(__('Invalid discount code.'));
            }

            $this->logger->log('Coupon code ' . $code . ' applied successfully.');

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
