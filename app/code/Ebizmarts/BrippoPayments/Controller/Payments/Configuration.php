<?php

namespace Ebizmarts\BrippoPayments\Controller\Payments;

use Ebizmarts\BrippoPayments\Helper\SoftFailRecover;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Model\Config\Source\ExpressLocation;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentElementStandalone;
use Ebizmarts\BrippoPayments\Model\ExpressCheckoutElement;
use Ebizmarts\BrippoPayments\Model\PaymentElement;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Configuration extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $storeManager;
    protected $dataHelper;
    protected $stripeHelper;
    protected $urlBuilder;
    protected $formKey;
    protected $softFailRecoverHelper;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param StoreManagerInterface $storeManager
     * @param DataHelper $dataHelper
     * @param Stripe $stripeHelper
     * @param UrlInterface $urlBuilder
     * @param FormKey $formKey
     * @param SoftFailRecover $softFailRecoverHelper
     */
    public function __construct(
        Context                             $context,
        JsonFactory                         $jsonFactory,
        Logger                              $logger,
        StoreManagerInterface               $storeManager,
        DataHelper                          $dataHelper,
        Stripe                              $stripeHelper,
        UrlInterface                        $urlBuilder,
        FormKey                             $formKey,
        SoftFailRecover                     $softFailRecoverHelper
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->stripeHelper = $stripeHelper;
        $this->urlBuilder = $urlBuilder;
        $this->formKey = $formKey;
        $this->softFailRecoverHelper = $softFailRecoverHelper;
    }

    public function execute()
    {
        try {
            $scopeId = $this->storeManager->getStore()->getId();
            $formKey = $this->formKey->getFormKey();
            $urlParams = [
                'form_key' => $formKey
            ];

            $response = [
                'valid' => 1,
                'apiVersion' => '2024-06-20',
                'publishableKey' => $this->dataHelper->getPlatformPublishableKey($scopeId),
                'general' => [
                    'controllers' => [
                        'log' => $this->urlBuilder->getUrl(
                            'brippo_payments/payments/log',
                            $urlParams
                        ),
                        'paymentStatus' => $this->urlBuilder->getUrl(
                            'brippo_payments/payments/status',
                            $urlParams
                        ),
                        'logOrderEvent' => $this->urlBuilder->getUrl(
                            'brippo_payments/payments/orderEvent',
                            $urlParams
                        ),
                        'analytic' => $this->urlBuilder->getUrl(
                            'brippo_payments/payments/analytic',
                            $urlParams
                        ),
                    ],
                    'allowFailedPayments' => $this->dataHelper->getStoreConfig(
                        SoftFailRecover::CONFIG_PATH_SOFT_FAIL_RECOVERY,
                        $scopeId
                    ),
                    'allowFailedPaymentsSecondAttempt' => $this->dataHelper->getStoreConfig(
                        SoftFailRecover::CONFIG_PATH_ALLOW_SECOND_ATTEMPT,
                        $scopeId
                    ),
                    'errorCodesAllowedForRecovery' => $this->softFailRecoverHelper->getAllowedErrorCodes($scopeId),
                ],
                'expressCheckoutElement' => [
                    'enabled' => $this->dataHelper->getStoreConfig(
                        ExpressCheckoutElement::XML_PATH_ACTIVE,
                        $scopeId
                    ),
                    'controllers' => [
                        'addCoupon' => $this->urlBuilder->getUrl(
                            'brippo_payments/expressCheckoutElement/addCoupon',
                            $urlParams
                        ),
                        'addShippingAddress' => $this->urlBuilder->getUrl(
                            'brippo_payments/expressCheckoutElement/addShippingAddress',
                            $urlParams
                        ),
                        'addShippingMethod' => $this->urlBuilder->getUrl(
                            'brippo_payments/expressCheckoutElement/addShippingMethod',
                            $urlParams
                        ),
                        'addToCart' => $this->urlBuilder->getUrl(
                            'brippo_payments/expressCheckoutElement/addToCart',
                            $urlParams
                        ),
                        'cancel' => $this->urlBuilder->getUrl(
                            'brippo_payments/expressCheckoutElement/cancel',
                            $urlParams
                        ),
                        'complete' => $this->urlBuilder->getUrl(
                            'brippo_payments/expressCheckoutElement/complete',
                            $urlParams
                        ),
                        'paymentRequest' => $this->urlBuilder->getUrl(
                            'brippo_payments/expressCheckoutElement/paymentRequest',
                            $urlParams
                        ),
                        'placeOrder' => $this->urlBuilder->getUrl(
                            'brippo_payments/expressCheckoutElement/placeOrder',
                            $urlParams
                        ),
                        'removeCoupon' => $this->urlBuilder->getUrl(
                            'brippo_payments/expressCheckoutElement/removeCoupon',
                            $urlParams
                        ),
                        'recoverOrder' => $this->urlBuilder->getUrl(
                            'brippo_payments/expressCheckoutElement/recoverOrder',
                            $urlParams
                        ),
                    ],
                    ExpressLocation::CART => [
                        'enabled' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_CART,
                            $scopeId
                        ),
                        'placementSelector' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_CART_PLACEMENT,
                            $scopeId
                        ),
                        'placementMode' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_CART_PLACEMENT_MODE,
                            $scopeId
                        ),
                        'orSeparator' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_CART_OR_SEPARATOR,
                            $scopeId
                        ),
                        'minAmount' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_CART_MIN_AMOUNT,
                            $scopeId
                        ),
                        'maxAmount' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_CART_MAX_AMOUNT,
                            $scopeId
                        ),
                    ],
                    ExpressLocation::PRODUCT_PAGE => [
                        'enabled' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_PRODUCT,
                            $scopeId
                        ),
                        'placementSelector' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_PRODUCT_PLACEMENT,
                            $scopeId
                        ),
                        'placementMode' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_PRODUCT_PLACEMENT_MODE,
                            $scopeId
                        ),
                        'orSeparator' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_PRODUCT_OR_SEPARATOR,
                            $scopeId
                        ),
                        'minAmount' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_PRODUCT_MIN_AMOUNT,
                            $scopeId
                        ),
                        'maxAmount' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_PRODUCT_MAX_AMOUNT,
                            $scopeId
                        ),
                    ],
                    ExpressLocation::MINICART => [
                        'enabled' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_MINICART,
                            $scopeId
                        ),
                        'placementSelector' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_MINICART_PLACEMENT,
                            $scopeId
                        ),
                        'placementMode' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_MINICART_PLACEMENT_MODE,
                            $scopeId
                        ),
                        'orSeparator' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_MINICART_OR_SEPARATOR,
                            $scopeId
                        ),
                        'minAmount' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_MINICART_MIN_AMOUNT,
                            $scopeId
                        ),
                        'maxAmount' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_MINICART_MAX_AMOUNT,
                            $scopeId
                        ),
                        'eventType' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_MINICART_EVENT_TYPE,
                            $scopeId
                        )
                    ],
                    ExpressLocation::CHECKOUT => [
                        'enabled' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_CHECKOUT,
                            $scopeId
                        ),
                        'placementSelector' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_CHECKOUT_PLACEMENT,
                            $scopeId
                        ),
                        'placementMode' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_CHECKOUT_PLACEMENT_MODE,
                            $scopeId
                        ),
                        'orSeparator' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_CHECKOUT_OR_SEPARATOR,
                            $scopeId
                        ),
                        'title' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_CHECKOUT_TITLE,
                            $scopeId
                        ),
                        'minAmount' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_CHECKOUT_MIN_AMOUNT,
                            $scopeId
                        ),
                        'maxAmount' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_CHECKOUT_MAX_AMOUNT,
                            $scopeId
                        ),
                    ],
                    ExpressLocation::CHECKOUT_LIST => [
                        'enabled' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_CHECKOUT_LIST,
                            $scopeId
                        ),
                        'minAmount' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_CHECKOUT_MIN_AMOUNT_LIST,
                            $scopeId
                        ),
                        'maxAmount' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LOCATIONS_CHECKOUT_MAX_AMOUNT_LIST,
                            $scopeId
                        ),
                    ],
                    'blockedCustomerGroups' => $this->dataHelper->getStoreConfig(
                        ExpressCheckoutElement::XML_PATH_BLOCKED_CUSTOMER_GROUPS,
                        $scopeId
                    ),
                    'pickupInputValues' => $this->dataHelper->getStoreConfig(
                        ExpressCheckoutElement::CONFIG_PATH_PICKUP_INPUT_VALUES,
                        $scopeId
                    ),
                ],
                'paymentElement' => [
                    'controllers' => [
                        'cancel' => $this->urlBuilder->getUrl(
                            'brippo_payments/paymentElement/cancel',
                            $urlParams
                        ),
                        'paymentRequest' => $this->urlBuilder->getUrl(
                            'brippo_payments/paymentElement/paymentRequest',
                            $urlParams
                        ),
                        'placeOrder' => $this->urlBuilder->getUrl(
                            'brippo_payments/paymentElement/placeOrder',
                            $urlParams
                        ),
                        'response' => $this->urlBuilder->getUrl(
                            'brippo_payments/paymentElement/response',
                            $urlParams
                        ),
                        'recoverOrder' => $this->urlBuilder->getUrl(
                            'brippo_payments/paymentElement/recoverOrder',
                            $urlParams
                        ),
                    ],
                    'businessName' => $this->dataHelper->getStoreDomain(),
                    'layout' => $this->dataHelper->getStoreConfig(
                        PaymentElement::XML_PATH_LAYOUT,
                        $scopeId
                    ),
                ],
                'paymentElementStandalone' => [
                    'enabled' => $this->dataHelper->getStoreConfig(
                        PaymentElementStandalone::XML_PATH_ACTIVE,
                        $scopeId
                    ),
                    'title' => $this->dataHelper->getStoreConfig(
                        PaymentElementStandalone::CONFIG_TITLE,
                        $scopeId
                    ),
                    'paymentMethod' => $this->dataHelper->getStoreConfig(
                        PaymentElementStandalone::CONFIG_PAYMENT_METHOD,
                        $scopeId
                    )
                ]
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
