<?php

namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite\Helper\ValidateRequest;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Helper\Data;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\ManagerInterface;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Api\Data\PayPalRequest;

class PayPalRequestManagement implements \Ebizmarts\SagePaySuite\Api\PayPalManagementInterface
{
    /** @var \Magento\Quote\Api\CartRepositoryInterface */
    private $quoteRepository;

    /** @var Config */
    private $config;

    /** @var \Magento\Checkout\Model\Session */
    private $checkoutSession;

    /** @var \Magento\Quote\Model\Quote */
    private $quote;

    /** @var \Magento\Framework\UrlInterface */
    private $coreUrl;

    /** @var Data */
    private $suiteHelper;

    /** @var \Ebizmarts\SagePaySuite\Helper\Request */
    private $requestHelper;

    /** @var \Ebizmarts\SagePaySuite\Model\Api\Post */
    private $postApi;

    /** @var \Ebizmarts\SagePaySuite\Api\Data\Result */
    private $result;

    /** @var \Ebizmarts\SagePaySuite\Helper\Checkout */
    private $checkoutHelper;

    /** @var Logger */
    private $suiteLogger;

    /** @var \Magento\Quote\Model\QuoteIdMaskFactory */
    private $quoteIdMaskFactory;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /** @var ManagerInterface */
    private $eventManager;

    /** @var PayPalRequest */
    private $paypalRequest;

    /** @var ValidateRequest */
    private $validateRequest;

    /**
     * PayPalRequestManagement constructor.
     * @param Config $config
     * @param \Ebizmarts\SagePaySuite\Helper\Data $suiteHelper
     * @param Logger $suiteLogger
     * @param \Ebizmarts\SagePaySuite\Helper\Request $requestHelper
     * @param \Ebizmarts\SagePaySuite\Helper\Checkout $checkoutHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Magento\Framework\UrlInterface $coreUrl
     * @param \Ebizmarts\SagePaySuite\Api\Data\ResultInterface $result
     * @param Api\Post $postApi
     * @param EncryptorInterface $encryptor
     * @param ManagerInterface $eventManager
     * @param ValidateRequest $validateRequest
     */
    public function __construct(
        Config $config,
        Data $suiteHelper,
        \Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger $suiteLogger,
        \Ebizmarts\SagePaySuite\Helper\Request $requestHelper,
        \Ebizmarts\SagePaySuite\Helper\Checkout $checkoutHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Framework\UrlInterface $coreUrl,
        \Ebizmarts\SagePaySuite\Api\Data\ResultInterface $result,
        \Ebizmarts\SagePaySuite\Model\Api\Post $postApi,
        EncryptorInterface $encryptor,
        ManagerInterface $eventManager,
        ValidateRequest $validateRequest
    ) {
        $this->quoteRepository    = $quoteRepository;
        $this->config             = $config;
        $this->suiteHelper        = $suiteHelper;
        $this->checkoutSession    = $checkoutSession;
        $this->suiteLogger        = $suiteLogger;
        $this->requestHelper      = $requestHelper;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->coreUrl            = $coreUrl;
        $this->checkoutHelper     = $checkoutHelper;
        $this->result             = $result;
        $this->postApi            = $postApi;
        $this->encryptor          = $encryptor;
        $this->eventManager       = $eventManager;
        $this->validateRequest    = $validateRequest;

        $this->config->setMethodCode($this->getMethodCode());
    }

    /**
     * @inheritDoc
     */
    public function savePaymentInformationAndPlaceOrder($cartId, $requestData)
    {
        try {
            //prepare quote
            $quote = $this->getQuoteById($cartId);

            $this->suiteHelper->addValidationHashToQuote($quote);

            $quote->collectTotals();
            $quote->reserveOrderId()->save();

            $this->quote = $quote;
            $this->paypalRequest = $requestData;
            //generate POST request
            $requestData = $this->generateRequest();

            //send POST to Sage Pay
            $postResponse = $this->postApi->sendPost(
                $requestData,
                $this->getServiceURL(),
                ["PPREDIRECT"],
                'Invalid response from PayPal'
            );

            //set payment info for save order
            $payment = $quote->getPayment();
            $payment->setMethod($this->getMethodCode());

            //save order with pending payment
            $order = $this->checkoutHelper->placeOrder($quote);

            if ($order) {
                //set pre-saved order flag in checkout session
                $this->checkoutSession->setData(
                    \Ebizmarts\SagePaySuite\Model\SessionInterface::PRESAVED_PENDING_ORDER_KEY,
                    $order->getId()
                );
                $this->checkoutSession->setData(
                    \Ebizmarts\SagePaySuite\Model\SessionInterface::CONVERTING_QUOTE_TO_ORDER,
                    1
                );

                //set payment data
                $payment = $order->getPayment();

                $transactionId = str_replace(["}", "{"], [""], $postResponse["data"]["VPSTxId"]);
                $payment->setTransactionId($transactionId);

                $payment->setLastTransId($transactionId);
                $payment->setAdditionalInformation('vendorTxCode', $requestData["VendorTxCode"]);
                $payment->setAdditionalInformation('vendorname', $this->config->getVendorname());
                $payment->setAdditionalInformation('mode', $this->config->getMode());
                $payment->setAdditionalInformation('paymentAction', $this->config->getSagepayPaymentAction());
                $payment->setAdditionalInformation('securityKey', $postResponse["data"]["SecurityKey"]);
                $payment->save();

                $this->eventManager->dispatch('checkout_submit_all_after', ['order' => $order, 'quote' => $quote]);

                //prepare response
                $this->result->setSuccess(true);
                $this->result->setResponse($postResponse);
            } else {
                throw new \Magento\Framework\Validator\Exception(__('Unable to save Elavon order'));
            }

            $this->result->setSuccess(true);
            $this->result->setResponse($postResponse);
        } catch (Api\ApiException $apiException) {
            $this->suiteLogger->logException($apiException, [__METHOD__, __LINE__]);

            $this->result->setSuccess(false);
            $this->result->setErrorMessage(
                __('Something went wrong: %1', $apiException->getUserMessage())
            );
        } catch (\Exception $e) {
            $this->suiteLogger->logException($e, [__METHOD__, __LINE__]);

            $this->result->setSuccess(false);
            $this->result->setErrorMessage(
                __('Something went wrong: %1', $e->getMessage())
            );
        }

        return $this->result;
    }

    /**
     * @return array
     */
    public function generateRequest()
    {
        $this->validateRequest->validateAddress($this->quote);
        $data                 = [];
        $data["VPSProtocol"]  = $this->config->getVPSProtocol();
        $data["TxType"]       = $this->config->getSagepayPaymentAction();
        $data["Vendor"]       = $this->config->getVendorname();
        $data["VendorTxCode"] = $this->suiteHelper->generateVendorTxCode($this->quote->getReservedOrderId());
        $data["Description"]  = __("Magento ecom transaction.");

        //referrer id
        $data["ReferrerID"] = $this->requestHelper->getReferrerId();

        if ($this->config->getBasketFormat() != Config::BASKETFORMAT_DISABLED) {
            $forceXmlBasket = $this->config->isPaypalForceXml();

            $basket = $this->requestHelper->populateBasketInformation($this->quote, $forceXmlBasket);
            $data   = array_merge($data, $basket);
        }

        $data["CardType"] = "PAYPAL";

        //populate payment amount information
        $data = array_merge($data, $this->requestHelper->populatePaymentAmountAndCurrency($this->quote));

        $data = $this->requestHelper->unsetBasketXMLIfAmountsDontMatch($data);

        //address information
        $data = array_merge($data, $this->requestHelper->populateAddressInformation($this->quote));

        $data["PayPalCallbackURL"] = $this->getCallbackUrl();
        $data["BillingAgreement"]  = (int)$this->config->getPaypalBillingAgreement();
        if ($this->config->shouldAllowRepeatTransactions()) {
            // COF
            $data['COFUsage'] = 'FIRST';
            $data['InitiatedType'] = 'CIT';
            $data['MITType'] = 'UNSCHEDULED';
        }

        return $data;
    }

    public function getQuoteRepository()
    {
        return $this->quoteRepository;
    }

    public function getQuoteIdMaskFactory()
    {
        return $this->quoteIdMaskFactory;
    }

    /**
     * @inheritDoc
     */
    public function getQuoteById($cartId)
    {
        return $this->getQuoteRepository()->get($cartId);
    }

    private function getServiceURL()
    {
        if ($this->config->getMode()== \Ebizmarts\SagePaySuite\Model\Config::MODE_LIVE) {
            return \Ebizmarts\SagePaySuite\Model\Config::URL_DIRECT_POST_LIVE;
        } else {
            return \Ebizmarts\SagePaySuite\Model\Config::URL_DIRECT_POST_TEST;
        }
    }

    private function getCallbackUrl()
    {
        $url = $this->coreUrl->getUrl('elavon/paypal/processing', [
            '_nosid' => true,
            '_secure' => true,
            '_store'  => $this->quote->getStoreId()
        ]);

        $url .= "?quoteid=" . urlencode($this->encryptor->encrypt($this->quote->getId()));

        return $url;
    }

    /**
     * @return string
     */
    private function getMethodCode()
    {
        return \Ebizmarts\SagePaySuite\Model\Config::METHOD_PAYPAL;
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getCart()
    {
        return $this->quote;
    }

    /**
     * @return PayPalRequest
     */
    public function getPayPalRequestData()
    {
        return $this->paypalRequest;
    }

    public function getResult()
    {
        return $this->result;
    }
}
