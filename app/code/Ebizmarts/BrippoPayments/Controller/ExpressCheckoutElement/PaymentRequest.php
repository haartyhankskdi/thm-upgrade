<?php

namespace Ebizmarts\BrippoPayments\Controller\ExpressCheckoutElement;

use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Helper\ExpressCheckoutElement as ExpressCheckoutElementHelper;
use Ebizmarts\BrippoPayments\Model\Config\Source\EceApplePayStyle;
use Ebizmarts\BrippoPayments\Model\Config\Source\EceGooglePayStyle;
use Ebizmarts\BrippoPayments\Model\Config\Source\EceLayoutOverflow;
use Ebizmarts\BrippoPayments\Model\Config\Source\ExpressLocation;
use Ebizmarts\BrippoPayments\Model\ExpressCheckoutElement;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class PaymentRequest extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $checkoutSession;
    protected $dataHelper;
    protected $eceHelper;
    protected $quoteFactory;
    protected $storeManager;
    protected $stripeHelper;
    protected $registry;
    protected $orderRepository;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param DataHelper $dataHelper
     * @param CheckoutSession $checkoutSession
     * @param ExpressCheckoutElementHelper $eceHelper
     * @param QuoteFactory $quoteFactory
     * @param StoreManagerInterface $storeManager
     * @param Stripe $stripeHelper
     * @param Registry $registry
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context                             $context,
        JsonFactory                         $jsonFactory,
        Logger                              $logger,
        DataHelper                          $dataHelper,
        CheckoutSession                     $checkoutSession,
        ExpressCheckoutElementHelper        $eceHelper,
        QuoteFactory                        $quoteFactory,
        StoreManagerInterface               $storeManager,
        Stripe                              $stripeHelper,
        Registry                            $registry,
        OrderRepositoryInterface            $orderRepository
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->eceHelper = $eceHelper;
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
        $this->stripeHelper = $stripeHelper;
        $this->registry = $registry;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        try {
            $this->eceHelper->setParamsFromRequestBody($this->getRequest());
            $scopeId = $this->storeManager->getStore()->getId();
            $placementId = $this->getRequest()->getParam('placementId');
            $currentProductId = $this->getRequest()->getParam('currentProductId');
            $recoverOrderId = $this->getRequest()->getParam('recoverOrderId');

            $amount = 0;
            $lineItems = [];
            $couponCode = null;
            $shouldRequestShipping = false;
            $shippingOptionsToSend = [];

            if ($placementId === ExpressLocation::RECOVER_CHECKOUT) {
                $order = $this->orderRepository->get($recoverOrderId);
                if (empty($order) || empty($order->getEntityId())) {
                    throw new LocalizedException(__('Order not found'));
                }
                $amount = $order->getGrandTotal();
                $currency = $this->eceHelper->getModalCurrencyFromOrder($order, $scopeId);
                $lineItems = $this->eceHelper->getLineItemsFromOrder($order, strtolower($currency));
            } else {
                $quote = $this->checkoutSession->getQuote();
                $currency = $this->eceHelper->getModalCurrencyFromQuote($quote, $scopeId);
                $shippingOptionsToSend = $this->eceHelper->getShippingOptions($quote, $currency);
                $amount = $quote->getGrandTotal();

                if ($placementId === ExpressLocation::PRODUCT_PAGE
                    && (empty($quote->getId()) || $quote->getGrandTotal() == 0)
                    && !empty($currentProductId)) {
                    $quote = $this->eceHelper->createQuoteFromScratch((int)$currentProductId);
                }

                $lineItems = $this->eceHelper->getLineItems($quote, strtolower($currency));
                $couponCode = $quote->getCouponCode();
                $shouldRequestShipping = $this->eceHelper->shouldRequestShipping(
                    $quote,
                    $placementId == null ? "" : $placementId,
                    $scopeId
                );
            }

            if ($amount == 0) {
                /*
                 * If no product options yet, the cart might be 0. Should be updated afterwards with product options.
                 */
                $amount = 1;
            }

            $response = [
                'valid' => 1,
                'options' => [
                    'mode' => 'payment',
                    'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount($amount, strtolower($currency)),
                    'captureMethod' => 'automatic',
                    'currency' => strtolower($currency),
                    'onBehalfOf' => $this->dataHelper->getAccountId(
                        $scopeId,
                        $this->dataHelper->isLiveMode()
                    ),
                    'paymentMethodCreation' => 'manual',
                    'appearance' => [
                        'theme' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_THEME,
                            $scopeId
                        ),
                        'variables' => [
                            'borderRadius' => max(0, min(20, (int)$this->dataHelper->getStoreConfig(
                                ExpressCheckoutElement::XML_PATH_STYLE_BUTTONS_CORNER_RADIUS,
                                $scopeId
                            ))) . 'px',
                            'fontSizeBase' => max(16, (int)$this->dataHelper->getStoreConfig(
                                ExpressCheckoutElement::XML_PATH_STYLE_FONT_SIZE_BASE,
                                $scopeId
                            )) . 'px'
                        ]
                    ]
                ],
                'checkoutOptions' => [
                    'lineItems' => $lineItems,
                    'emailRequired' => true,
                    'phoneNumberRequired' => true,
                    'business' => [
                        'name' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_STORE_BUSINESS_NAME,
                            $scopeId
                        )
                    ]
                    //'allowedShippingCountries' => ['US'] //toDo dynamic
                ],
                'elementsOptions' => [
                    'paymentMethods' => [
                        'applePay' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_WALLETS_APPLE_PAY,
                            $scopeId
                        ),
                        'googlePay' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_WALLETS_GOOGLE_PAY,
                            $scopeId
                        ),
                        'link' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_WALLETS_LINK,
                            $scopeId
                        )
                    ],
                    'paymentMethodOrder' => $this->eceHelper->getPaymentMethodsOrder(),
                    'layout' => [
                        'maxColumns' => (int)$this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LAYOUT_MAX_COLS,
                            $scopeId
                        ),
                        'maxRows' => (int)$this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_LAYOUT_MAX_ROWS,
                            $scopeId
                        ),
                        'overflow' => $this->getValidOverflowValue($scopeId)
                    ],
                    'buttonHeight' => (int)$this->dataHelper->getStoreConfig(
                        ExpressCheckoutElement::XML_PATH_STYLE_BUTTONS_HEIGHT,
                        $scopeId
                    ),
                    'buttonTheme' => [
                        'applePay' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_STYLE_APPLE_PAY,
                            $scopeId
                        ),
                        'googlePay' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_STYLE_GOOGLE_PAY,
                            $scopeId
                        )
                    ],
                    'buttonType' => [
                        'applePay' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_TYPE_APPLE_PAY,
                            $scopeId
                        ),
                        'googlePay' => $this->dataHelper->getStoreConfig(
                            ExpressCheckoutElement::XML_PATH_TYPE_GOOGLE_PAY,
                            $scopeId
                        )
                    ]
                ],
                'customer' => [
                    'groupId' => $this->checkoutSession->getQuote()->getCustomerGroupId()
                ],
                'coupon' => $couponCode
            ];

            //remove invalid
            if ($response['elementsOptions']['buttonTheme']['applePay'] == EceApplePayStyle::AUTO) {
                unset($response['elementsOptions']['buttonTheme']['applePay']);
            }
            if ($response['elementsOptions']['buttonTheme']['googlePay'] == EceGooglePayStyle::AUTO) {
                unset($response['elementsOptions']['buttonTheme']['googlePay']);
            }
            if (empty($response['elementsOptions']['buttonTheme'])) {
                unset($response['elementsOptions']['buttonTheme']);
            }
            if (empty($response['checkoutOptions']['business']['name'])) {
                unset($response['checkoutOptions']['business']);
            }

            if ($shouldRequestShipping) {
                $response['checkoutOptions']['shippingAddressRequired'] = true;
                if (count($shippingOptionsToSend) > 0) {
                    $response['checkoutOptions']['shippingRates'] = $shippingOptionsToSend;
                } else {
                    $response['checkoutOptions']['shippingRates'] = [[
                        'id' => 'calculating-shipping-rates',
                        'displayName' => 'Calculating shipping rates...',
                        'amount' => 0
                    ]];
                }
            } else {
                $response['checkoutOptions']['shippingAddressRequired'] = false;
            }

            $this->eceHelper->generateOrderUniqId($this->checkoutSession);
            $this->checkoutSession->setBrippoRequestedAmount($amount);
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

    /**
     * Get valid overflow value based on maxRows configuration
     * overflow: 'never' is only supported when maxRows is 0
     * Use overflow: 'auto' instead when maxRows != 0
     *
     * @param int $scopeId
     * @return string
     * @throws NoSuchEntityException
     */
    private function getValidOverflowValue($scopeId): string
    {
        $maxRows = (int)$this->dataHelper->getStoreConfig(
            ExpressCheckoutElement::XML_PATH_LAYOUT_MAX_ROWS,
            $scopeId
        );

        $configuredOverflow = $this->dataHelper->getStoreConfig(
            ExpressCheckoutElement::XML_PATH_LAYOUT_OVERFLOW,
            $scopeId
        );

        // If maxRows is not 0 and overflow is set to 'never', use 'auto' instead
        if ($maxRows != 0 && $configuredOverflow === EceLayoutOverflow::NEVER) {
            return EceLayoutOverflow::AUTO;
        }

        return $configuredOverflow;
    }
}
