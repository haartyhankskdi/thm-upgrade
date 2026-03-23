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
use Magento\Store\Model\StoreManagerInterface;

class AddShippingAddress extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $dataHelper;
    protected $checkoutSession;
    protected $expressHelper;
    protected $storeManager;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param DataHelper $dataHelper
     * @param CheckoutSession $checkoutSession
     * @param ExpressHelper $expressHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context                             $context,
        JsonFactory                         $jsonFactory,
        Logger                              $logger,
        DataHelper                          $dataHelper,
        CheckoutSession                     $checkoutSession,
        ExpressHelper                       $expressHelper,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->expressHelper = $expressHelper;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $this->expressHelper->setParamsFromRequestBody($this->getRequest());
        $shippingAddress = $this->getRequest()->getParam('shippingAddress');

        try {
            $quote = $this->checkoutSession->getQuote();
            $scopeId = $this->storeManager->getStore()->getId();
            $currency = $this->expressHelper->getModalCurrencyFromQuote($quote, $scopeId);

            $this->expressHelper->setShippingAddressToQuoteFromPaymentRequestData(
                $quote,
                $shippingAddress,
                $currency
            );

            if ($this->expressHelper->isGBPostalCodeAndNeedsFixing($shippingAddress)) {
                $shippingAddress = $this->expressHelper->fixGBPostalCode($shippingAddress);
                $this->expressHelper->setShippingAddressToQuoteFromPaymentRequestData(
                    $quote,
                    $shippingAddress,
                    $currency
                );
            }
            $shippingOptionsToSend = $this->expressHelper->getShippingOptions($quote, $currency);

            if (empty($shippingOptionsToSend)) {
                // phpcs:disable
                $this->logger->log(print_r($shippingAddress, true));
                // phpcs:enable
                //toDo revert to previous address as no shipping address event will be thrown again
                throw new LocalizedException(__('No shipping options available for your address.'));
            }

            if (empty($quote->getShippingAddress()->getShippingMethod()) && isset($shippingOptionsToSend[0]['id'])) {
                $this->logger->log("No shipping method is set, setting shipping method to "
                    . $shippingOptionsToSend[0]['id']);
                $this->expressHelper->setShippingMethodToQuote($quote, $shippingOptionsToSend[0]['id']);
            }

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
            $error = $this->expressHelper->prettifyErrorMessage($ex->getMessage(), [], $shippingAddress);
            $this->logger->log($error);
            $response = [
                'valid' => 0,
                'message' => $error,
                'updateDetails' => [
                    'status' => 'invalid_shipping_address'
                ]
            ];
        }

        $resultJson = $this->jsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }
}
