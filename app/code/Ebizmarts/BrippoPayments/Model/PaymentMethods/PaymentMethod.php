<?php

namespace Ebizmarts\BrippoPayments\Model\PaymentMethods;

use Ebizmarts\BrippoPayments\Block\Payment\Info;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents as BrippoPaymentIntentsApi;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Payments as PaymentsHelper;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Exception;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger as MagentoLogger;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order;

abstract class PaymentMethod extends AbstractMethod
{
    const ADDITIONAL_DATA_PAYMENT_INTENT_ID     = 'payment_intent_id';
    const ADDITIONAL_DATA_TRANSFER_CHARGE_ID    = 'transfer_charge_id';
    const ADDITIONAL_DATA_LIVEMODE              = 'livemode';
    const ADDITIONAL_DATA_CARD_COUNTRY          = 'card_country';
    const ADDITIONAL_DATA_CARD_BRAND            = 'card_brand';
    const ADDITIONAL_DATA_CARD_LAST4            = 'card_last4';
    const ADDITIONAL_DATA_CARD_BRAND_PRODUCT    = 'card_brand_product';
    const ADDITIONAL_DATA_CARD_EXP_MONTH        = 'card_exp_month';
    const ADDITIONAL_DATA_CARD_EXP_YEAR         = 'card_exp_year';
    const ADDITIONAL_DATA_WALLET                = 'wallet';
    const ADDITIONAL_DATA_RECEIPT_NUMBER        = 'receipt_number';
    const ADDITIONAL_DATA_FRONTEND_SOURCE       = 'source';
    const ADDITIONAL_DATA_STATUS                = 'status';
    const ADDITIONAL_DATA_CURRENCY              = 'currency';
    const ADDITIONAL_DATA_RADAR_RISK            = 'radar_risk';
    const ADDITIONAL_DATA_STREET_CHECK          = 'street_check';
    const ADDITIONAL_DATA_ZIP_CHECK             = 'zip_check';
    const ADDITIONAL_DATA_CVC_CHECK             = 'cvc_check';
    const ADDITIONAL_DATA_PAYMENT_LINK_ID       = 'payment_link_id';
    const ADDITIONAL_DATA_RECOVER_TRIES         = 'recover_tries';
    const ADDITIONAL_DATA_WAS_EMAIL_SENT        = 'was_email_sent';
    const ADDITIONAL_DATA_EMAIL_SEND_TRIES      = 'email_send_tries';
    const ADDITIONAL_DATA_PAYMENT_LINK_URL      = 'payment_link_url';
    const ADDITIONAL_DATA_FRAUD_NOT_AVAILABLE   = 'fraud_data_not_available';
    const ADDITIONAL_DATA_FUNDING               = 'funding';
    const ADDITIONAL_DATA_TIMELINE              = 'timeline';
    const ADDITIONAL_DATA_LAST_PAYMENT_ERROR    = 'last_payment_error';
    const ADDITIONAL_DATA_3D_SECURE             = 'three_d_secure';
    const ADDITIONAL_DATA_PASSED                = 'passed';
    const ADDITIONAL_DATA_REJECTED              = 'rejected';
    const ADDITIONAL_DATA_ATTEMPTED             = 'attempted';
    const ADDITIONAL_DATA_NOT_PRESENT           = 'not_present';
    const ADDITIONAL_DATA_FAILED                = 'failed';

    protected $_infoBlockType = Info::class;

    protected $brippoLogger;
    protected $brippoApiPaymentIntents;
    protected $dataHelper;
    protected $paymentsHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param MagentoLogger $logger
     * @param Logger $brippoLogger
     * @param DataHelper $dataHelper
     * @param BrippoPaymentIntentsApi $brippoApiPaymentIntents
     * @param PaymentsHelper $paymentsHelper
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param DirectoryHelper|null $directory
     */
    public function __construct(
        Context                     $context,
        Registry                   $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory      $customAttributeFactory,
        Data                       $paymentData,
        ScopeConfigInterface       $scopeConfig,
        MagentoLogger              $logger,
        Logger                     $brippoLogger,
        DataHelper                 $dataHelper,
        BrippoPaymentIntentsApi    $brippoApiPaymentIntents,
        PaymentsHelper             $paymentsHelper,
        ?AbstractResource           $resource = null,
        ?AbstractDb                 $resourceCollection = null,
        array                      $data = [],
        ?DirectoryHelper            $directory = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data,
            $directory
        );
        $this->brippoLogger = $brippoLogger;
        $this->brippoApiPaymentIntents = $brippoApiPaymentIntents;
        $this->dataHelper = $dataHelper;
        $this->paymentsHelper = $paymentsHelper;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function isAvailable(?CartInterface $quote = null)
    {
        if ($quote && $quote->getShippingAddress()) {
            $shippingCountry = $quote->getShippingAddress()->getCountryId();
            $specificCountriesActive = $this->dataHelper->getStoreConfig(
                DataHelper::XML_PATH_APPLICABLE_COUNTRY_ACTIVE
            );
            $allowedCountries = $this->dataHelper->getStoreConfig(DataHelper::XML_PATH_APPLICABLE_COUNTRY);

            if ($specificCountriesActive
                && $allowedCountries
                && !in_array($shippingCountry, explode(',', (string)$allowedCountries))) {
                return false;
            }

            if (!$this->dataHelper->isAboveMinimumAmountAllowed($quote)) {
                return false;
            }
        }

        return parent::isAvailable($quote);
    }

    /**
     * @param InfoInterface $payment
     * @param $amount
     * @return $this|PaymentMethod
     * @throws LocalizedException
     */
    public function capture(InfoInterface $payment, $amount)
    {
        if ($amount > 0) {
            try {
                /** @var Order $order */
                $order = $payment->getOrder();
                $transactionId = $payment->getTransactionId();

                if (!$transactionId) {
                    $paymentIntentId = $payment->getAdditionalInformation(self::ADDITIONAL_DATA_PAYMENT_INTENT_ID);
                    $liveMode = $payment->getAdditionalInformation(self::ADDITIONAL_DATA_LIVEMODE);

                    if (empty($paymentIntentId)) {
                        throw new LocalizedException(__('Capture failed. Invalid Payment Intent ID.'));
                    }

                    $currency = $payment->getAdditionalInformation(
                        PaymentMethod::ADDITIONAL_DATA_LIVEMODE
                    );
                    if (empty($currency)) {
                        $currency = $this->paymentsHelper->getPaymentCurrency($order);
                    }

                    $amountToCapture = $amount;
                    if (strtoupper($currency) !== $order->getBaseCurrencyCode() &&
                        $order->getBaseGrandTotal() != $order->getGrandTotal()) {
                        /*
                        * Amend for different currency
                        */
                        $rate = $order->getGrandTotal() / $order->getBaseGrandTotal();
                        $amountToCapture = min($rate * $amountToCapture, $order->getGrandTotal());
                    }

                    $paymentIntent = $this->brippoApiPaymentIntents->capture(
                        $paymentIntentId,
                        $liveMode,
                        $amountToCapture,
                        $currency
                    );

                    if ($paymentIntent['status'] != Stripe::PAYMENT_INTENT_STATUS_SUCCEEDED) {
                        throw new LocalizedException(
                            __('Capture failed. Payment Intent status is ' . $paymentIntent['status'] . '.')
                        );
                    }

                    $payment->setTransactionId($paymentIntent['id']);
                    $payment->setParentTransactionId($payment->getTransactionId());

                    $this->brippoLogger->log('Order #' . $order->getIncrementId() .
                        ' successfully captured.');
                }
            } catch (Exception $ex) {
                $this->brippoLogger->log($ex->getMessage());
                throw $ex;
            }
        }

        return $this;
    }

    /**
     * @param InfoInterface $payment
     * @param $amount
     * @return $this|PaymentMethod
     * @throws Exception
     */
    public function refund(InfoInterface $payment, $amount)
    {
        try {
            if ($amount > 0) {
                /** @var Order $order */
                $order = $payment->getOrder();
                $paymentIntentId = $payment->getAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_PAYMENT_INTENT_ID
                );
                $liveMode = $payment->getAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_LIVEMODE
                );

                $currency = $payment->getAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_LIVEMODE
                );
                if (empty($currency)) {
                    $currency = $this->paymentsHelper->getPaymentCurrency($order);
                }

                $amountToRefund = $amount;
                if ($currency !== $order->getBaseCurrencyCode() &&
                    $order->getBaseGrandTotal() != $order->getGrandTotal()) {
                    /*
                    * Amend for different currency
                    */
                    $rate = $order->getGrandTotal() / $order->getBaseGrandTotal();
                    $amountToRefund = min($rate * $amountToRefund, $order->getGrandTotal());
                }

                $refund = $this->brippoApiPaymentIntents->refund(
                    $this->dataHelper->getAccountId(
                        $order->getStoreId(),
                        $liveMode ?? true,
                        $order->getStore()->getScopeType()
                    ),
                    $paymentIntentId,
                    $amountToRefund,
                    $currency,
                    $liveMode ?? true
                );

                if (empty($refund) || !isset($refund['id'])) {
                    throw new LocalizedException(__("Online refund failed to process, please try again."));
                }

                $payment->setTransactionId($refund['id']);

                $this->brippoLogger->log('Charge ' . $payment->getRefundTransactionId()
                    . " successfully refunded for Order #" . $order->getIncrementId() . '.');
            }

            $payment->setIsTransactionClosed(1);
            $payment->setShouldCloseParentTransaction(1);
        } catch (Exception $ex) {
            $this->brippoLogger->log($ex->getMessage());
            throw $ex;
        }

        return $this;
    }
}
