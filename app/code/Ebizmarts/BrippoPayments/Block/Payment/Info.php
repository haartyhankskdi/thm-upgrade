<?php

namespace Ebizmarts\BrippoPayments\Block\Payment;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents as BrippoPaymentIntentsApi;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentLinks as BrippoPaymentLinksApi;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\Refunds as BrippoRefundsApi;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Model\Express;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;
use Ebizmarts\BrippoPayments\Model\TerminalBackend;
use Exception;
use Magento\Directory\Model\Country;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\ConfigurableInfo;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\InfoInterface;
use Ebizmarts\BrippoPayments\Helper\PlatformService\Receipts as PlatformReceiptsService;
use Magento\Store\Model\ScopeInterface;

class Info extends ConfigurableInfo
{
    protected $_template = 'payment/info.phtml';

    public $paymentIntent = null;
    public $paymentLink = null;
    public $cards = [];
    public $subscription = null;
    protected $country;
    protected $info;
    protected $registry;
    protected $logger;
    protected $json;
    protected $dataHelper;
    public $stripeHelper;
    public $priceCurrency;
    protected $brippoApiPaymentIntents;
    protected $brippoApiPaymentLinks;
    protected $brippoApiRefunds;
    protected $platformReceiptsService;

    /**
     * @param Context $context
     * @param ConfigInterface $config
     * @param Country $country
     * @param \Magento\Payment\Model\Info $info
     * @param Json $json
     * @param Registry $registry
     * @param Logger $logger
     * @param DataHelper $dataHelper
     * @param Stripe $stripeHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param BrippoPaymentIntentsApi $brippoApiPaymentIntents
     * @param BrippoPaymentLinksApi $brippoApiPaymentLinks
     * @param BrippoRefundsApi $brippoApiRefunds
     * @param PlatformReceiptsService $platformReceiptsService
     * @param array $data
     */
    public function __construct(
        Context                     $context,
        ConfigInterface             $config,
        Country                     $country,
        \Magento\Payment\Model\Info $info,
        Json                        $json,
        Registry                    $registry,
        Logger                      $logger,
        DataHelper                  $dataHelper,
        Stripe                      $stripeHelper,
        PriceCurrencyInterface      $priceCurrency,
        BrippoPaymentIntentsApi     $brippoApiPaymentIntents,
        BrippoPaymentLinksApi       $brippoApiPaymentLinks,
        BrippoRefundsApi            $brippoApiRefunds,
        PlatformReceiptsService     $platformReceiptsService,
        array                       $data = []
    ) {
        parent::__construct($context, $config, $data);
        $this->country = $country;
        $this->info = $info;
        $this->registry = $registry;
        $this->logger = $logger;
        $this->json = $json;
        $this->dataHelper = $dataHelper;
        $this->stripeHelper = $stripeHelper;
        $this->priceCurrency = $priceCurrency;
        $this->brippoApiPaymentIntents = $brippoApiPaymentIntents;
        $this->brippoApiPaymentLinks = $brippoApiPaymentLinks;
        $this->brippoApiRefunds = $brippoApiRefunds;
        $this->platformReceiptsService = $platformReceiptsService;
    }

    /**
     * @param $walletProvider
     * @return string
     */
    public function getWalletLogoSrc($walletProvider): string
    {
        switch ($walletProvider) {
            case "Google Pay":
            case "google_pay":
                return $this->getViewFileUrl(
                    "Ebizmarts_BrippoPayments::img/googlepay.png",
                    [
                        'area'  => 'frontend'
                    ]
                );
            case "Apple Pay":
            case "apple_pay":
                return $this->getViewFileUrl(
                    "Ebizmarts_BrippoPayments::img/applepay.svg",
                    [
                        'area'  => 'frontend'
                    ]
                );
            case "Link":
            case "link":
                return $this->getViewFileUrl(
                    "Ebizmarts_BrippoPayments::img/link.png",
                    [
                        'area'  => 'frontend'
                    ]
                );
        }
        return '';
    }

    /**
     * @return mixed|null
     */
    public function getOrder()
    {
        return $this->registry->registry('current_order');
    }

    /**
     * @return InfoInterface
     * @throws LocalizedException
     */
    public function getPayment()
    {
        $order = $this->getOrder();

        if ($order != null) {
            $payment = $order->getPayment();
        } else {
            $payment = $this->getMethod()->getInfoInstance();
        }
        return $payment;
    }

    /**
     * @return bool
     */
    public function isOurMethod(): bool
    {
        return $this->dataHelper->wasOrderPaidWithBrippo($this->getOrder());
    }

    /**
     * @return array|false|mixed|null
     */
    public function getPaymentIntent()
    {
        if (!$this->isOurMethod()) {
            return null;
        }

        if (!empty($this->paymentIntent)) {
            return $this->paymentIntent;
        }

        if ($this->paymentIntent === false) {
            return false;
        }

        try {
            $paymentIntentId = $this->getPayment()->getAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_PAYMENT_INTENT_ID
            );
            $liveMode = $this->getPayment()->getAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_LIVEMODE
            );
            if ($paymentIntentId) {
                $this->paymentIntent = $this->brippoApiPaymentIntents->get(
                    $paymentIntentId,
                    $liveMode ?? true
                );
            } else {
                return false;
            }
        } catch (Exception $ex) {
            $this->paymentIntent = false;
            $this->logger->log($ex->getMessage());
        }

        return $this->paymentIntent;
    }

    /**
     * @return array|false|null
     * @throws LocalizedException
     */
    public function getPaymentLink()
    {
        if (!$this->isOurMethod()) {
            return null;
        }

        if (!empty($this->paymentLink)) {
            return $this->paymentLink;
        }

        if ($this->paymentLink === false) {
            return false;
        }

        try {
            $paymentLinkId = $this->getPayment()->getAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_PAYMENT_LINK_ID
            );
            $liveMode = $this->getPayment()->getAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_LIVEMODE
            );
            if ($paymentLinkId) {
                $this->paymentLink = $this->brippoApiPaymentLinks->get(
                    $paymentLinkId,
                    $liveMode ?? true
                );
            } else {
                return false;
            }
        } catch (Exception $ex) {
            $this->paymentLink = false;
            $this->logger->log($ex->getMessage());
        }

        return $this->paymentLink;
    }

    /**
     * @return mixed|string
     */
    public function getPaymentIntentStatus()
    {
        if (!empty($this->getPaymentIntent())) {
            $status = $this->getPaymentIntent()[Stripe::PARAM_STATUS];
            if (!empty($this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE])) {
                if ($status === 'succeeded') {
                    if ($this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE][Stripe::PARAM_AMOUNT_REFUNDED] ===
                        $this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE][Stripe::PARAM_AMOUNT_CAPTURED]) {
                        $status = 'refunded';
                    } elseif ($this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE]
                        [Stripe::PARAM_AMOUNT_REFUNDED] > 0) {
                        $status = 'partial-refund';
                    }
                }
            }
            return $status;
        }
        return '';
    }

    /**
     * @return false|string
     */
    public function getPaymentIntentAuthoriationExpires()
    {
        $captureBeforeDate = '?';
        if (!empty($this->getPaymentIntent())
            && !empty($this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE])) {
            $cardData = $this->stripeHelper->getCardData($this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE]);
            if (!empty($cardData[Stripe::PARAM_CAPTURE_BEFORE])) {
                $captureBeforeDate = gmdate("F j, Y, g:i a", $cardData[Stripe::PARAM_CAPTURE_BEFORE]);
            }
        }

        return $captureBeforeDate;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getPaymentLinkStatus()
    {
        if (!empty($this->getPaymentLink())) {
            $status = $this->getPaymentLink()[Stripe::PARAM_ACTIVE];
            if ($status === true) {
                $status = 'active';
            } else {
                $status = 'canceled';
            }
            return $status;
        }
        return '';
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getPaymentLinkEmailSent()
    {
        if ($this->getPayment()->getAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_WAS_EMAIL_SENT
        )) {
            return __('Yes')->getText();
        }
        return __('No')->getText();
    }

    /**
     * @return mixed|string
     */
    public function getPaymentIntentId()
    {
        if (!empty($this->getPaymentIntent())) {
            return $this->getPaymentIntent()[Stripe::PARAM_ID];
        }
        return '';
    }

    /**
     * @return mixed|string
     * @throws LocalizedException
     */
    public function getPaymentLinkId()
    {
        if (!empty($this->getPaymentLink())) {
            return $this->getPaymentLink()[Stripe::PARAM_ID];
        }
        return '';
    }

    /**
     * @return mixed|string
     * @throws LocalizedException
     */
    public function getPaymentLinkUrl()
    {
        if (!empty($this->getPaymentLink())) {
            return $this->getPaymentLink()[Stripe::PARAM_URL];
        }
        return '';
    }

    /**
     * @param $data
     * @return string|null
     */
    public function prepareCardExpDate($data)
    {
        if (isset($data['card_exp_month']) && isset($data['card_exp_year'])) {
            return $data['card_exp_month'] . "/" . $data['card_exp_year'];
        }

        return null;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getPaymentIntentAmount()
    {
        if (!empty($this->getPaymentIntent())) {
            return $this->priceCurrency->format(
                $this->stripeHelper->convertStripeAmountToMagentoAmount(
                    $this->getPaymentIntent()[Stripe::PARAM_AMOUNT],
                    $this->getPaymentIntent()[Stripe::PARAM_CURRENCY]
                ),
                false,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                null,
                strtoupper($this->getPaymentIntent()[Stripe::PARAM_CURRENCY])
            );
        }
        return '';
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getPaymentIntentCapturedAmount()
    {
        if (!empty($this->getPaymentIntent())) {
            if (!empty($this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE])) {
                return $this->priceCurrency->format(
                    $this->stripeHelper->convertStripeAmountToMagentoAmount(
                        $this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE][Stripe::PARAM_AMOUNT_CAPTURED],
                        $this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE][Stripe::PARAM_CURRENCY]
                    ),
                    false,
                    PriceCurrencyInterface::DEFAULT_PRECISION,
                    null,
                    strtoupper($this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE][Stripe::PARAM_CURRENCY])
                );
            } else {
                return $this->priceCurrency->format(
                    0,
                    false,
                    PriceCurrencyInterface::DEFAULT_PRECISION,
                    null,
                    strtoupper($this->getPaymentIntent()[Stripe::PARAM_CURRENCY])
                );
            }
        }
        return '';
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getPaymentIntentRefundedAmount()
    {
        if (!empty($this->getPaymentIntent())) {
            if (!empty($this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE])) {
                return $this->priceCurrency->format(
                    $this->stripeHelper->convertStripeAmountToMagentoAmount(
                        $this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE][Stripe::PARAM_AMOUNT_REFUNDED],
                        $this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE][Stripe::PARAM_CURRENCY]
                    ),
                    false,
                    PriceCurrencyInterface::DEFAULT_PRECISION,
                    null,
                    strtoupper($this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE][Stripe::PARAM_CURRENCY])
                );
            } else {
                return $this->priceCurrency->format(
                    0,
                    false,
                    PriceCurrencyInterface::DEFAULT_PRECISION,
                    null,
                    strtoupper($this->getPaymentIntent()[Stripe::PARAM_CURRENCY])
                );
            }
        }
        return '';
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getPaymentIntentError()
    {
        $message = '';
        if (!empty($this->getPaymentIntent())) {
            if (!empty($this->getPaymentIntent()[Stripe::PARAM_LAST_PAYMENT_ERROR])) {
                $message = $this->getPaymentIntent()[Stripe::PARAM_LAST_PAYMENT_ERROR]['message'];
            } else {
                $charge = $this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE];
                if (isset($charge[Stripe::PARAM_OUTCOME][Stripe::PARAM_SELLER_MESSAGE])
                    && $charge[Stripe::PARAM_OUTCOME][Stripe::PARAM_TYPE] !== 'authorized') {
                    $message = $charge[Stripe::PARAM_OUTCOME][Stripe::PARAM_SELLER_MESSAGE];
                } elseif (isset($charge[Stripe::PARAM_FAILURE_MESSAGE])) {
                    $message = $charge[Stripe::PARAM_FAILURE_MESSAGE];
                }
            }
        }

        return $this->prettifyErrorMessage($message);
    }

    /**
     * @param $message
     * @return string
     */
    protected function prettifyErrorMessage($message): string
    {
        try {
            if (
                strpos(
                    $message,
                    'The provided PaymentMethod has failed authentication'
                ) !== false
                || strpos(
                    $message,
                    'The latest payment attempt of this PaymentIntent has failed or been canceled, and the attached payment method has been removed'
                ) !== false
            ) {
                return 'The customer failed 3D Secure authentication';
            }
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
        }

        return $message;
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function wasRefunded(): bool
    {
        $paymentIntent = $this->getPaymentIntent();
        if (!empty($paymentIntent) &&
            !empty($paymentIntent[Stripe::PARAM_LATEST_CHARGE])
        ) {
            return $paymentIntent[Stripe::PARAM_LATEST_CHARGE][Stripe::PARAM_AMOUNT_REFUNDED] > 0;
        }

        return false;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getRefunds()
    {
        $refunds = [];
        $paymentIntent = $this->getPaymentIntent();
        if (isset($paymentIntent[Stripe::PARAM_LATEST_CHARGE][Stripe::PARAM_TRANSFER][Stripe::PARAM_REVERSALS])
            && !empty($paymentIntent)
        ) {
            $reversals = $paymentIntent[Stripe::PARAM_LATEST_CHARGE][Stripe::PARAM_TRANSFER]
                [Stripe::PARAM_REVERSALS]['data'];
            $reversalsCount = count($reversals);
            for ($i = 0; $i<$reversalsCount; $i++) {
                $sourceRefundId = $reversals[$i][Stripe::PARAM_SOURCE_REFUND];

                if (empty($sourceRefundId)) {
                    $refunds []= [
                        'id' => $sourceRefundId,
                        'error' => 'No refund ID found.'
                    ];
                    continue;
                }

                try {
                    $refund = $this->brippoApiRefunds->get(
                        $sourceRefundId,
                        $this->getPayment()->getAdditionalInformation(
                            PaymentMethod::ADDITIONAL_DATA_LIVEMODE
                        ) ?? true
                    );
                    $refunds []= $refund;
                } catch (Exception $ex) {
                    $refunds []= [
                        'id' => $sourceRefundId,
                        'error' => $ex->getMessage()
                    ];
                }
            }
        }

        return $refunds;
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getTitle()
    {
        return $this->getPayment()->getTitle();
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function isExpressCheckout()
    {
        return $this->getPayment()->getMethodInstance()->getCode() === Express::METHOD_CODE;
    }

    /**
     * @return string
     */
    public function getWallet(): string
    {
        $paymentIntent = $this->getPaymentIntent();
        if (!empty($paymentIntent) &&
            !empty($paymentIntent[Stripe::PARAM_LATEST_CHARGE])
        ) {
            $cardData = $this->stripeHelper->getCardData($paymentIntent[Stripe::PARAM_LATEST_CHARGE]);
            if (isset($cardData[Stripe::PARAM_WALLET][Stripe::PARAM_TYPE])) {
                return (string)$cardData[Stripe::PARAM_WALLET][Stripe::PARAM_TYPE];
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        $paymentIntent = $this->getPaymentIntent();
        if (!empty($paymentIntent) &&
            isset($paymentIntent[Stripe::PARAM_LATEST_CHARGE][Stripe::PARAM_PAYMENT_METHOD_DETAILS][Stripe::PARAM_TYPE])
        ) {
            return (string)$paymentIntent[Stripe::PARAM_LATEST_CHARGE][Stripe::PARAM_PAYMENT_METHOD_DETAILS][Stripe::PARAM_TYPE];
        }

        return '';
    }

    /**
     * @return ?string
     */
    public function getReceiptNumber(): ?string
    {
        try {
            return $this->getPayment()->getAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_RECEIPT_NUMBER
            );
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getReceiptPdfUrl(): string
    {
        try {
            $receiptNumber = $this->getReceiptNumber();
            if (empty($receiptNumber)) {
                return '';
            }

            return $this->platformReceiptsService->getReceiptPdfUrl(
                ScopeInterface::SCOPE_STORE,
                $this->getOrder()->getStoreId(),
                $this->getPaymentIntent()[Stripe::PARAM_ID],
                $this->getReceiptNumber(),
                $this->platformReceiptsService->getReceiptDescription($this->getOrder())
            );
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * @param string $paymentMethod
     * @return Phrase|string
     */
    public function getPaymentMethodName(string $paymentMethod)
    {
        switch ($paymentMethod) {
            case 'card':
                return __('Card');
            case 'card_present':
                return __('Card present');
            case 'klarna':
                return __('Klarna');
            case 'afterpay_clearpay':
                return __('Afterpay / Clearpay');
            case 'pay_by_bank':
                return __('Pay by Bank');
            default:
                return $paymentMethod;
        }
    }

    /**
     * @return mixed|string
     * @throws LocalizedException
     */
    public function getSource()
    {
        return $this->getPayment()->getAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_FRONTEND_SOURCE
        ) ?? "";
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getCardBrand(): string
    {
        $paymentIntent = $this->getPaymentIntent();
        if (!empty($paymentIntent) &&
            !empty($paymentIntent[Stripe::PARAM_LATEST_CHARGE])
        ) {
            $cardData = $this->stripeHelper->getCardData($paymentIntent[Stripe::PARAM_LATEST_CHARGE]);
            if (!empty($cardData) && !empty($cardData['brand'])) {
                return $cardData['brand'];
            }
        }

        return "-";
    }

    /**
     * @return string
     */
    public function getCardCountry(): string
    {
        $paymentIntent = $this->getPaymentIntent();
        if (!empty($paymentIntent) &&
            !empty($paymentIntent[Stripe::PARAM_LATEST_CHARGE])
        ) {
            $cardData = $this->stripeHelper->getCardData($paymentIntent[Stripe::PARAM_LATEST_CHARGE]);
            if (!empty($cardData) && !empty($cardData['country'])) {
                return $cardData['country'];
            }
        }

        return "-";
    }

    /**
     * @return string
     */
    public function getCardExpDate(): string
    {
        $paymentIntent = $this->getPaymentIntent();
        if (!empty($paymentIntent) &&
            !empty($paymentIntent[Stripe::PARAM_LATEST_CHARGE])
        ) {
            $cardData = $this->stripeHelper->getCardData($paymentIntent[Stripe::PARAM_LATEST_CHARGE]);
            if (!empty($cardData) && !empty($cardData['exp_month'])) {
                return $cardData['exp_month'] . '/' . $cardData['exp_year'];
            }
        }

        return "-";
    }

    /**
     * @return string
     */
    public function getCardBrandProduct(): string
    {
        $paymentIntent = $this->getPaymentIntent();
        if (!empty($paymentIntent) &&
            !empty($paymentIntent[Stripe::PARAM_LATEST_CHARGE])
        ) {
            $cardData = $this->stripeHelper->getCardData($paymentIntent[Stripe::PARAM_LATEST_CHARGE]);
            if (!empty($cardData) && !empty($cardData['brand_product'])) {
                return $cardData['brand_product'];
            }
        }

        return "-";
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getCardLast4(): string
    {
        $paymentIntent = $this->getPaymentIntent();
        if (!empty($paymentIntent) &&
            !empty($paymentIntent[Stripe::PARAM_LATEST_CHARGE])
        ) {
            $cardData = $this->stripeHelper->getCardData($paymentIntent[Stripe::PARAM_LATEST_CHARGE]);
            if (!empty($cardData) && !empty($cardData['last4'])) {
                return $cardData['last4'];
            }
        }

        return "-";
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getFunding(): string
    {
        $paymentIntent = $this->getPaymentIntent();
        if (!empty($paymentIntent) &&
            !empty($paymentIntent[Stripe::PARAM_LATEST_CHARGE])
        ) {
            $cardData = $this->stripeHelper->getCardData($paymentIntent[Stripe::PARAM_LATEST_CHARGE]);
            if (!empty($cardData) && !empty($cardData['funding'])) {
                return $cardData['funding'];
            }
        }

        return "-";
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getFraudRisk(): string
    {
        if ($this->getPaymentIntent()) {
            if (!empty($this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE]) &&
                !empty($this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE][Stripe::PARAM_OUTCOME]) &&
                !empty($this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE][Stripe::PARAM_OUTCOME]
                [Stripe::PARAM_RISK_LEVEL])) {
                $value = $this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE][Stripe::PARAM_OUTCOME]
                [Stripe::PARAM_RISK_LEVEL];
                return str_replace('_', ' ', $value);
            }
        }

        return 'unknown';
    }

    /**
     * @return mixed|string|null
     * @throws LocalizedException
     */
    public function getStreetCheck()
    {
        if ($this->getPaymentIntent()) {
            if (!empty($this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE])) {
                return $this->stripeHelper->getStreetCheck($this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE]);
            }
        }

        return "unchecked";
    }

    /**
     * @return mixed|string|null
     * @throws LocalizedException
     */
    public function getZipCheck()
    {
        if ($this->getPaymentIntent()) {
            if (!empty($this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE])) {
                return $this->stripeHelper->getZipCheck($this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE]);
            }
        }

        return 'unchecked';
    }

    /**
     * @return mixed|string|null
     * @throws LocalizedException
     */
    public function getCVCCheck()
    {
        if ($this->getPaymentIntent()) {
            if (!empty($this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE])) {
                return $this->stripeHelper->getCVCCheck($this->getPaymentIntent()[Stripe::PARAM_LATEST_CHARGE]);
            }
        }

        return 'unchecked';
    }

    /**
     * @return bool
     */
    public function supportsTerminalProcessing(): bool
    {
        try {
            $paymentMethodCode = $this->getPayment()->getMethod();
            return $paymentMethodCode === TerminalBackend::METHOD_CODE;
        } catch (Exception $e) {
            return false;
        }
    }
}
