<?php

namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite\Api\Data\ScaTransTypeInterface;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultInterface;
use Ebizmarts\SagePaySuite\Helper\Checkout;
use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Helper\Request;
use Ebizmarts\SagePaySuite\Model\Api\PIRest;
use Ebizmarts\SagePaySuite\Model\Config\SagePayCardType;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Ebizmarts\SagePaySuite\Model\ResourceModel\MsScaData;
use Ebizmarts\SagePaySuite\Model\SessionInterface as SagePaySession;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Helper\BrowserData;
use Magento\Checkout\Model\Session;
use Magento\Framework\UrlInterface;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;

class MsPayment
{
    /**
     * The purpose of the below constants is to define the request's parameters size.
     */
    private const FIRST_NAME_START = 0;
    private const FIRST_NAME_LENGTH = 20;
    private const LAST_NAME_START = 0;
    private const LAST_NAME_LENGTH = 20;
    private const TELEPHONE_START = 0;
    private const TELEPHONE_LENGTH = 20;
    private const CITY_START = 0;
    private const CITY_LENGTH = 40;
    private const POST_CODE_START = 0;
    private const POST_CODE_LENGTH = 10;
    private const COUNTRY_START = 0;
    private const COUNTRY_LENGTH = 2;
    private const STATE_START = 0;
    private const STATE_LENGTH = 2;
    private const STREET_START = 0;
    private const STREET_LENGTH = 100;

    private const NUM_OF_ATTEMPTS = 5;
    private const RETRY_INTERVAL = 6000000;

    /** @var Multishipping */
    private $multishipping;

    /** @var Config */
    private $sagepayConfig;

    /** @var Session */
    private $checkoutSession;

    /** @var Request */
    private $requestHelper;

    /** @var Data */
    private $suiteHelper;

    /** @var string */
    private $vendorTxCode;

    /** @var PIRest */
    private $piRest;

    /** @var PiTransactionResultInterface */
    private $payResult;

    /** @var Logger */
    private $suiteLogger;

    /** @var Checkout */
    private $checkoutHelper;

    /** @var InvoiceSender */
    private $invoiceEmailSender;

    private $okStatuses = [
        Config::SUCCESS_STATUS,
        Config::AUTH3D_V2_REQUIRED_STATUS
    ];

    /** @var SagePayCardType */
    private $ccConverter;

    /** @var OrderLoader */
    private $orderLoader;

    /** @var MsScaData */
    private $scaDataResourceModel;

    /** @var UrlInterface */
    private $coreUrl;

    /** @var CryptAndCodeData */
    private $cryptAndCode;

    /** @var BrowserData */
    private $browserData;

    /**
     * MsPayment constructor.
     * @param Multishipping $multishipping
     * @param Config $sagepayConfig
     * @param Session $checkoutSession
     * @param Request $requestHelper
     * @param Data $suiteHelper
     * @param PIRest $piRest
     * @param PiTransactionResultInterface $payResult
     * @param Logger $suiteLogger
     * @param Checkout $checkoutHelper
     * @param InvoiceSender $invoiceEmailSender
     * @param SagePayCardType $ccConverter
     * @param OrderLoader $orderLoader
     * @param MsScaData $scaDataResourceModel
     * @param UrlInterface $coreUrl
     * @param CryptAndCodeData $cryptAndCode
     * @param BrowserData $browserData
     */
    public function __construct(
        Multishipping $multishipping,
        Config $sagepayConfig,
        Session $checkoutSession,
        Request $requestHelper,
        Data $suiteHelper,
        PIRest $piRest,
        PiTransactionResultInterface $payResult,
        Logger $suiteLogger,
        Checkout $checkoutHelper,
        InvoiceSender $invoiceEmailSender,
        SagePayCardType $ccConverter,
        OrderLoader $orderLoader,
        MsScaData $scaDataResourceModel,
        UrlInterface $coreUrl,
        CryptAndCodeData $cryptAndCode,
        BrowserData $browserData
    ) {
        $this->multishipping = $multishipping;
        $this->sagepayConfig = $sagepayConfig;
        $this->checkoutSession = $checkoutSession;
        $this->requestHelper = $requestHelper;
        $this->suiteHelper = $suiteHelper;
        $this->piRest = $piRest;
        $this->piRest->setPaymentMethod();
        $this->payResult = $payResult;
        $this->suiteLogger = $suiteLogger;
        $this->checkoutHelper = $checkoutHelper;
        $this->invoiceEmailSender = $invoiceEmailSender;
        $this->ccConverter = $ccConverter;
        $this->orderLoader = $orderLoader;
        $this->scaDataResourceModel = $scaDataResourceModel;
        $this->coreUrl = $coreUrl;
        $this->cryptAndCode = $cryptAndCode;
        $this->browserData = $browserData;
    }

    /**
     * @param $orderIds
     * @return PiTransactionResultInterface
     */
    public function placeTransactions($orderIds)
    {
        /** @var PiTransactionResultInterface $response */
        $response = null;
        /** @var OrderInterface $order */
        $order = null;
        $amount = 0;
        foreach ($orderIds as $orderId) {
            $order = $this->orderLoader->loadOrderById($orderId);
            $amount = $amount + $order->getGrandTotal();
        }
        try {
            if ($order != null) {
                $requestData = $this->_generateRequestData($order, $amount, $orderIds);
                $response = $this->piRest->capture($requestData);
                $this->setPayResult($response);

                if ($response->getStatusCode() != Config::AUTH3D_REQUIRED_STATUS) {
                    $this->_processPayment($orderIds);
                }
            }
        } catch (\Exception $e) {
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $e->getTraceAsString(), [__METHOD__, __LINE__]);
        }

        return $this->payResult;
    }

    /**
     * @param OrderInterface $order
     * @param float $amount
     * @param null|array $orderIds
     * @return array
     */
    private function _generateRequestData($order, $amount, $orderIds = null)
    {
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        $cardIdentifier = $this->checkoutSession->getData(SagePaySession::CARD_IDENTIFIER);
        $merchantSessionKey = $this->checkoutSession->getData(SagePaySession::MERCHANT_SESSION_KEY);
        $vendorTxCode = $this->suiteHelper->generateVendorTxCode($order->getIncrementId());
        $this->setVendorTxCode($vendorTxCode);

        $data = [
            'transactionType' => $this->sagepayConfig->getSagepayPaymentAction(),
            'paymentMethod'   => [
                'card'        => [
                    'merchantSessionKey' => $merchantSessionKey,
                    'cardIdentifier'     => $cardIdentifier,
                ]
            ],
            'vendorTxCode'      => $vendorTxCode,
            'description'       => $this->requestHelper->getOrderDescription(),
            'customerFirstName' => substr(
                trim($billingAddress->getFirstname()),
                self::FIRST_NAME_START,
                self::FIRST_NAME_LENGTH
            ),
            'customerLastName'  => substr(
                trim($billingAddress->getLastname()),
                self::LAST_NAME_START,
                self::LAST_NAME_LENGTH
            ),
            'apply3DSecure'     => $this->sagepayConfig->get3Dsecure(),
            'applyAvsCvcCheck'  => $this->sagepayConfig->getAvsCvc(),
            'referrerId'        => $this->requestHelper->getReferrerId(),
            'customerEmail'     => $billingAddress->getEmail(),
            'customerPhone'     => substr(
                trim($billingAddress->getTelephone()),
                self::TELEPHONE_START,
                self::TELEPHONE_LENGTH
            ),
        ];

        $data['entryMethod'] = 'Ecommerce';

        $data['billingAddress'] = [
            'address1'      => $this->getBillingStreet($billingAddress),
            'city'          => substr(trim($billingAddress->getCity()), self::CITY_START, self::CITY_LENGTH),
            'postalCode'    => substr(
                trim($this->sanitizePostcode($billingAddress->getPostcode())),
                self::POST_CODE_START,
                self::POST_CODE_LENGTH
            ),
            'country'       => substr(
                trim($billingAddress->getCountryId()),
                self::COUNTRY_START,
                self::POST_CODE_LENGTH
            )
        ];

        if ($data['billingAddress']['country'] == 'US') {
            $data['billingAddress']['state'] = substr(
                $billingAddress->getRegionCode(),
                self::STATE_START,
                self::STATE_LENGTH
            );
        } else {
            if ($data['billingAddress']['country'] == 'IE' &&
                $data['billingAddress']['postalCode'] == '') {
                $data['billingAddress']['postalCode'] = "000";
            } else {
                if ($data['billingAddress']['country'] == 'HK' &&
                    $data['billingAddress']['postalCode'] == '') {
                    $data['billingAddress']['postalCode'] = "000";
                }
            }
        }

        $data['shippingDetails'] = [
            'recipientFirstName' => substr(
                trim($shippingAddress->getFirstname()),
                self::FIRST_NAME_START,
                self::FIRST_NAME_LENGTH
            ),
            'recipientLastName'  => substr(
                trim($shippingAddress->getLastname()),
                self::LAST_NAME_START,
                self::LAST_NAME_LENGTH
            ),
            'shippingAddress1'   => substr(
                trim($shippingAddress->getStreetLine(1)),
                self::STREET_START,
                self::STREET_LENGTH
            ),
            'shippingCity'       => substr(
                trim($shippingAddress->getCity()),
                self::CITY_START,
                self::CITY_LENGTH
            ),
            'shippingPostalCode' => substr(
                trim($this->sanitizePostcode($shippingAddress->getPostcode())),
                self::POST_CODE_START,
                self::POST_CODE_LENGTH
            ),
            'shippingCountry'    => substr(
                trim($shippingAddress->getCountryId()),
                self::COUNTRY_START,
                self::POST_CODE_LENGTH
            )
        ];

        if ($data['shippingDetails']['shippingCountry'] == 'US') {
            $data['shippingDetails']['shippingState'] = substr(
                $shippingAddress->getRegionCode(),
                self::STATE_START,
                self::STATE_LENGTH
            );
        } else {
            if ($data['shippingDetails']['shippingCountry'] == 'IE' &&
                $data['shippingDetails']['shippingPostalCode'] == '') {
                $data['shippingDetails']['shippingPostalCode'] = "000";
            } else {
                if ($data['shippingDetails']['shippingCountry'] == 'HK' &&
                    $data['shippingDetails']['shippingPostalCode'] == '') {
                    $data['shippingDetails']['shippingPostalCode'] = "000";
                }
            }
        }

        $data['amount'] = $amount*100;
        $data['currency'] = $order->getOrderCurrencyCode();

        $quoteId = $order->getQuoteId();
        $scaData = $this->scaDataResourceModel->getScaDataByQuoteId($quoteId);

        $data['strongCustomerAuthentication'] = [
            'browserJavascriptEnabled' => 1,
            'browserJavaEnabled'       => (int)$scaData['java_enabled'],
            'browserColorDepth'        => $this->browserData->getBrowserColorDepth((int)$scaData['color_depth']),
            'browserScreenHeight'      => (int)$scaData['screen_height'],
            'browserScreenWidth'       => (int)$scaData['screen_width'],
            'browserTZ'                => (int)$scaData['time_zone'],
            'browserAcceptHeader'      => $scaData['accept_header'],
            'browserIP'                => $this->browserData->getBrowserIP(),
            'browserLanguage'          => $this->browserData->getBrowserLanguage($scaData['language']),
            'browserUserAgent'         => $scaData['user_agent'],
            'notificationURL'          => $this->_getNotificationUrl($quoteId, $orderIds),
            'transType'                => ScaTransTypeInterface::GOOD_SERVICE_PURCHASE,
            'challengeWindowSize'      => $this->sagepayConfig->getValue("challengewindowsize"),
        ];

        $data['credentialType'] = [
            'cofUsage'      => 'First',
            'initiatedType' => 'CIT',
            'mitType'       => 'Unscheduled'
        ];

        return $data;
    }

    /**
     * @return string
     */
    public function getVendorTxCode()
    {
        return $this->vendorTxCode;
    }

    /**
     * @param string $vendorTxCode
     */
    public function setVendorTxCode($vendorTxCode)
    {
        $this->vendorTxCode = $vendorTxCode;
    }

    /**
     * @param $text
     * @return string
     */
    private function sanitizePostcode($postCode)
    {
        return preg_replace("/[^a-zA-Z0-9-\s]/", "", $postCode);
    }

    /**
     * @return PiTransactionResultInterface
     */
    public function getPayResult()
    {
        return $this->payResult;
    }

    /**
     * @param PiTransactionResultInterface $payResult
     */
    public function setPayResult(PiTransactionResultInterface $payResult)
    {
        $this->payResult = $payResult;
    }

    /**
     * @param $orderIds
     */
    private function _processPayment($orderIds)
    {
        if ($this->_isSuccessOrThreeDAuth()) {
            foreach ($orderIds as $orderId) {
                $order = $this->orderLoader->loadOrderById($orderId);
                $payment = $order->getPayment();
                $this->_saveAdditionalPaymentInformation($payment);
                $this->_saveCreditCardInformationInPayment($payment);
                if ($this->getPayResult()->getStatusCode() == Config::SUCCESS_STATUS) {
                    //Invoice successful orders that don't require 3D authentication
                    $this->_createInvoice($payment, $order);
                }
            }
        }
    }

    /**
     * @return bool
     */
    private function _isSuccessOrThreeDAuth()
    {
        return in_array($this->getPayResult()->getStatusCode(), $this->okStatuses, true);
    }

    /**
     * @param OrderPaymentInterface $payment
     */
    private function _saveAdditionalPaymentInformation($payment)
    {
        $payResult = $this->getPayResult();
        $payment->setMethod(Config::METHOD_PI);
        $payment->setTransactionId($payResult->getTransactionId());
        $payment->setLastTransId($payResult->getTransactionId());
        $payment->setAdditionalInformation('statusCode', $payResult->getStatusCode());
        $payment->setAdditionalInformation('statusDetail', $payResult->getStatusDetail());

        if ($payResult->getThreeDSecure() !== null) {
            $payment->setAdditionalInformation('3DSecureStatus', $payResult->getThreeDSecure()->getStatus());
        }

        $avsCvcCheck = $payResult->getAvsCvcCheck();

        if ($avsCvcCheck !== null) {
            $payment->setAdditionalInformation('AVSCV2', $avsCvcCheck->getStatus());
            $payment->setAdditionalInformation('AddressResult', $avsCvcCheck->getAddress());
            $payment->setAdditionalInformation('PostCodeResult', $avsCvcCheck->getPostalCode());
            $payment->setAdditionalInformation('CV2Result', $avsCvcCheck->getSecurityCode());
        }

        $payment->setAdditionalInformation('vendorname', $this->sagepayConfig->getVendorname());
        $payment->setAdditionalInformation('mode', $this->sagepayConfig->getMode());
        $payment->setAdditionalInformation('paymentAction', $payResult->getTransactionType());
        $payment->setAdditionalInformation('bankAuthCode', $payResult->getBankAuthCode());
        $payment->setAdditionalInformation('txAuthNo', $payResult->getTxAuthNo());
        $payment->setAdditionalInformation('vendorTxCode', $this->getVendorTxCode());
        $payment->setAdditionalInformation('VPSTxId', $payResult->getTransactionId());

        $payment->save();
    }

    /**
     * @param OrderPaymentInterface $payment
     */
    private function _saveCreditCardInformationInPayment($payment)
    {
        if ($this->getPayResult()->getPaymentMethod() !== null) {
            $card = $this->getPayResult()->getPaymentMethod()->getCard();
            if ($card !== null) {
                $payment->setCcLast4($card->getLastFourDigits());
                $payment->setCcExpMonth($card->getExpiryMonth());
                $payment->setCcExpYear($card->getExpiryYear());
                $payment->setCcType($this->ccConverter->convert($card->getCardType()));
                $payment->save();
            }
        }
    }

    /**
     * @param OrderPaymentInterface $payment
     * @param $order
     */
    private function _createInvoice($payment, $order)
    {
        if ($this->getPayResult()->getTransactionType() === Config::ACTION_PAYMENT_PI) {
            $payment->getMethodInstance()->markAsInitialized();
        }

        try {
            $order->place()->save();
        } catch (\Exception $e) {
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $e->getMessage());
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $e->getTraceAsString(), [__METHOD__, __LINE__]);
        }

        $this->checkoutHelper->sendOrderEmail($order);
        $this->_sendInvoiceNotification($order);
    }

    /**
     * @param $order
     * @throws \Exception
     */
    private function _sendInvoiceNotification($order)
    {
        if ($this->_invoiceConfirmationIsEnable() && $this->_paymentActionIsCapture()) {
            $invoices = $order->getInvoiceCollection();
            if ($invoices->count() > 0) {
                $this->invoiceEmailSender->send($invoices->getFirstItem());
            }
        }
    }

    /**
     * @return bool
     */
    private function _invoiceConfirmationIsEnable()
    {
        return $this->sagepayConfig->getInvoiceConfirmationNotification() === "1";
    }

    /**
     * @return bool
     */
    private function _paymentActionIsCapture()
    {
        $sagePayPaymentAction = $this->sagepayConfig->getSagepayPaymentAction();
        return $sagePayPaymentAction === Config::ACTION_PAYMENT_PI;
    }

    /**
     * @param int $quoteId
     * @param $orderIds
     * @return string
     */
    private function _getNotificationUrl($quoteId, $orderIds)
    {
        $encryptedQuoteId = $this->_encryptAndEncode($quoteId);

        $url = $this->coreUrl->getUrl(
            'elavon/pi/callback3Dv2Multishipping',
            [
                '_store' => $this->sagepayConfig->getCurrentStoreId(),
                'quoteId' => $encryptedQuoteId,
                '_nosid' => true,
                '_secure' => true
            ]
        );

        $count = 0;

        foreach ($orderIds as $orderId) {
            if ($count > 0) {
                $url .= "/";
            }
            $url .= "orderId" . $count . "/" . $orderId;
            ++$count;
        }

        $url .= "/quantity/" . $count;

        return $url;
    }

    /**
     * @param $data
     * @return string
     */
    private function _encryptAndEncode($data)
    {
        return $this->cryptAndCode->encryptAndEncode($data);
    }

    /**
     * @param OrderAddressInterface $billingAddress
     * @return false|string
     */
    public function getBillingStreet(OrderAddressInterface $billingAddress)
    {
        return substr(trim($billingAddress->getStreetLine(1)), 0, 100);
    }
}
