<?php

namespace Ebizmarts\BrippoPayments\Helper;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents as BrippoPaymentIntentsApi;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentLinks as BrippoPaymentLinksApi;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\Service;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\Payments as PaymentsHelper;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Ebizmarts\BrippoPayments\Model\PayByLinkMoto;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;
use Magento\Backend\Model\UrlInterface as UrlBackend;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Store\Model\StoreManagerInterface;

class PayByLinkBackend extends PayByLink
{
    protected $storeManager;
    protected $url;

    /**
     * @param Context $context
     * @param PaymentHelper $magentoPaymentHelper
     * @param DataHelper $dataHelper
     * @param AddressRenderer $addressRenderer
     * @param TransportBuilder $transportBuilder
     * @param OrderManagementInterface $orderManagement
     * @param QuoteFactory $quoteFactory
     * @param BrippoPaymentIntentsApi $brippoApiPaymentIntents
     * @param BrippoPaymentLinksApi $brippoApiPaymentLinks
     * @param PaymentsHelper $paymentsHelper
     * @param OrderSender $orderSender
     * @param StoreManagerInterface $storeManager
     * @param UrlBackend $url
     * @param Stripe $stripeHelper
     * @param Json $json
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context                     $context,
        PaymentHelper               $magentoPaymentHelper,
        DataHelper                  $dataHelper,
        AddressRenderer             $addressRenderer,
        TransportBuilder            $transportBuilder,
        OrderManagementInterface    $orderManagement,
        QuoteFactory                $quoteFactory,
        BrippoPaymentIntentsApi     $brippoApiPaymentIntents,
        BrippoPaymentLinksApi       $brippoApiPaymentLinks,
        PaymentsHelper              $paymentsHelper,
        OrderSender                 $orderSender,
        StoreManagerInterface       $storeManager,
        UrlBackend                  $url,
        Stripe                      $stripeHelper,
        Json                        $json,
        EncryptorInterface          $encryptor
    ) {
        parent::__construct(
            $context,
            $magentoPaymentHelper,
            $dataHelper,
            $addressRenderer,
            $transportBuilder,
            $orderManagement,
            $quoteFactory,
            $brippoApiPaymentIntents,
            $brippoApiPaymentLinks,
            $paymentsHelper,
            $orderSender,
            $stripeHelper,
            $json,
            $encryptor
        );

        $this->storeManager = $storeManager;
        $this->url = $url;
    }

    /**
     * @param Order $order
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function processBackendOrder(Order $order): void
    {
        $this->dataHelper->logger->logOrderEvent(
            $order,
            'Order placed with ' . PayByLinkMoto::METHOD_CODE . '. Processing...'
        );

        if ($order->getIncrementId()) {
            $order->setState(Order::STATE_PENDING_PAYMENT)
                ->setStatus(BrippoOrder::STATUS_PENDING)
                ->save();

            $amount = $order->getGrandTotal();
            $currency = $order->getOrderCurrencyCode();
            $scopeId = $order->getStore()->getId();
            $liveMode = $this->dataHelper->isLiveMode($scopeId);
            $customerEmail = $order->getCustomerEmail();
            $quoteId = $order->getQuoteId();

            $accountId = $this->dataHelper->getAccountId(
                $scopeId,
                $liveMode
            );

            $paymentLink = $this->brippoApiPaymentLinks->create(
                $amount,
                $currency,
                $order->getIncrementId(),
                $accountId,
                \Ebizmarts\BrippoPayments\Model\PayByLink::KEY_HOSTED_CONFIRMATION,
                $this->dataHelper->getStoreConfig(
                    PayByLinkMoto::XML_PATH_CONFIG_HOSTED_CONFIRM_MSG,
                    $scopeId
                ),
                '',
                $this->paymentsHelper->getMetadataForPaymentIntent(
                    $accountId,
                    $customerEmail,
                    PayByLinkMoto::METHOD_CODE,
                    $order->getIncrementId(),
                    '',
                    $quoteId,
                    'checkout'
                ),
                $liveMode
            );

            $this->dataHelper->logger->logOrderEvent(
                $order,
                'Payment Link created with ID ' .
                $paymentLink[Service::PARAM_PL_ID] . '.'
            );

            $emailSent = $this->sendFinalizePaymentEmail(
                $scopeId,
                $paymentLink[Stripe::PARAM_URL],
                $order,
                $customerEmail
            );

            $order->getPayment()->setAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_PAYMENT_LINK_ID,
                $paymentLink[Service::PARAM_PL_ID]
            )->setAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_LIVEMODE,
                $liveMode
            )->setAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_PAYMENT_LINK_URL,
                $paymentLink[Stripe::PARAM_URL]
            )->setAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_WAS_EMAIL_SENT,
                $emailSent
            )->save();

            $this->dataHelper->logger->logOrderEvent(
                $order,
                'Pay by Link Backend process completed successfully. Awaiting payment...'
            );
        }
    }
}
