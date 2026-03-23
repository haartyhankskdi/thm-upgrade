<?php

namespace Ebizmarts\BrippoPayments\Controller\ExpressCheckoutElement;

use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Helper\ExpressCheckoutElement;
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
    protected $eceHelper;
    protected $quoteFactory;
    protected $stripeHelper;
    protected $storeManager;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param DataHelper $dataHelper
     * @param CheckoutSession $checkoutSession
     * @param ExpressCheckoutElement $eceHelper
     * @param QuoteFactory $quoteFactory
     * @param Stripe $stripeHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context                             $context,
        JsonFactory                         $jsonFactory,
        Logger                              $logger,
        DataHelper                          $dataHelper,
        CheckoutSession                     $checkoutSession,
        ExpressCheckoutElement              $eceHelper,
        QuoteFactory                        $quoteFactory,
        Stripe                              $stripeHelper,
        StoreManagerInterface               $storeManager
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->eceHelper = $eceHelper;
        $this->quoteFactory = $quoteFactory;
        $this->stripeHelper = $stripeHelper;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $this->eceHelper->setParamsFromRequestBody($this->getRequest());
        $shippingOption = $this->getRequest()->getParam('shippingOption');

        try {
            $quote = $this->checkoutSession->getQuote();
            $scopeId = $this->storeManager->getStore()->getId();
            $currency = $this->eceHelper->getModalCurrencyFromQuote($quote, $scopeId);

            $shippingOptionsToSend = $this->eceHelper->getShippingOptions($quote, $currency);
            if (empty($shippingOptionsToSend)) {
                throw new LocalizedException(__('No shipping options available for your address.'));
            }

            if (!$this->eceHelper->isShippingMethodAvailable($shippingOptionsToSend, $shippingOption['id'])) {
                throw new LocalizedException(__('Shipping option not available for your address.'));
            }

            $this->eceHelper->setShippingMethodToQuote($quote, $shippingOption['id']);

            $shippingOptionsToSend = $this->eceHelper->getShippingOptions($quote, $currency);
            if (empty($shippingOptionsToSend)) {
                throw new LocalizedException(__('No shipping options available for your address.'));
            }

            $response = [
                'valid' => 1,
                'options' => [
                    'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount(
                        $quote->getGrandTotal(),
                        strtolower($currency)
                    )
                ],
                'checkoutOptions' => [
                    'lineItems' => $this->eceHelper->getLineItems($quote, strtolower($currency)),
                    'shippingRates' => $shippingOptionsToSend
                ]
            ];
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            $this->logger->log(print_r($shippingOption ?? [], true));
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
