<?php

namespace Ebizmarts\BrippoPayments\Helper;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents as BrippoPaymentIntentsApi;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentLinks as BrippoPaymentLinksApi;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\PaymentMethods\PaymentMethod;
use Ebizmarts\BrippoPayments\Helper\Payments as PaymentsHelper;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Ebizmarts\BrippoPayments\Model\Config\Source\CaptureMethodPayByLink;
use Ebizmarts\BrippoPayments\Model\PayByLink as PayByLinkMethod;
use Ebizmarts\BrippoPayments\Model\PayByLinkMoto;
use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class PayByLink extends PaymentMethod
{
    protected $magentoPaymentHelper;
    protected $addressRenderer;
    protected $transportBuilder;
    protected $orderManagement;
    protected $quoteFactory;
    protected $brippoApiPaymentIntents;
    protected $brippoApiPaymentLinks;
    protected $paymentsHelper;
    protected $orderSender;
    protected $stripeHelper;

    /**
     * @param Context $context
     * @param PaymentHelper $magentoPaymentHelper
     * @param Data $dataHelper
     * @param AddressRenderer $addressRenderer
     * @param TransportBuilder $transportBuilder
     * @param OrderManagementInterface $orderManagement
     * @param QuoteFactory $quoteFactory
     * @param BrippoPaymentIntentsApi $brippoApiPaymentIntents
     * @param BrippoPaymentLinksApi $brippoApiPaymentLinks
     * @param Payments $paymentsHelper
     * @param OrderSender $orderSender
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
        Stripe                      $stripeHelper,
        Json                        $json,
        EncryptorInterface          $encryptor
    ) {
        parent::__construct($context, $dataHelper, $json, $encryptor);
        $this->magentoPaymentHelper = $magentoPaymentHelper;
        $this->addressRenderer = $addressRenderer;
        $this->transportBuilder = $transportBuilder;
        $this->orderManagement = $orderManagement;
        $this->quoteFactory = $quoteFactory;
        $this->brippoApiPaymentIntents = $brippoApiPaymentIntents;
        $this->brippoApiPaymentLinks = $brippoApiPaymentLinks;
        $this->paymentsHelper = $paymentsHelper;
        $this->orderSender = $orderSender;
        $this->stripeHelper = $stripeHelper;
    }

    /**
     * @param $scopeId
     * @param $paymentLinkUrl
     * @param OrderInterface $order
     * @param $emailAddress
     * @return bool
     */
    public function sendFinalizePaymentEmail($scopeId, $paymentLinkUrl, OrderInterface $order, $emailAddress): bool
    {
        try {
            $this->dataHelper->logger->logOrderEvent(
                $order,
                "Trying to send payment link's email..."
            );

            $emailTemplate = $this->dataHelper->getStoreConfig(
                PayByLinkMethod::XML_PATH_STORE_CONFIG_EMAIL_TEMPLATE,
                $scopeId
            );

            $legacyTemplatePattern = '/^payment_.*_brippo_payments_brippo_payments_paybylink_email_template$/';
            if (preg_match($legacyTemplatePattern, $emailTemplate)) {
                $emailTemplate = PayByLinkMethod::DEFAULT_TEMPLATE_ID;
            }

            $emailSender = $this->dataHelper->getStoreConfig(
                PayByLinkMethod::XML_PATH_STORE_CONFIG_EMAIL_SENDER,
                $scopeId
            );

            $transport = [
                'order' => $order,
                'order_id' => $order->getId(),
                'billing' => $order->getBillingAddress(),
                'payment_html' => $this->getPaymentHtml($order, $scopeId),
                'store' => $order->getStore(),
                'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
                'created_at_formatted' => $order->getCreatedAtFormatted(2),
                'order_data' => [
                    'customer_name' => $order->getCustomerName(),
                    'is_not_virtual' => $order->getIsNotVirtual(),
                    'email_customer_note' => $order->getEmailCustomerNote(),
                    'frontend_status_label' => $order->getFrontendStatusLabel()
                ],
                'payment_link' => $paymentLinkUrl
            ];
            $transportObject = new DataObject($transport);

            $this->transportBuilder->setTemplateVars($transportObject->getData());
            $this->transportBuilder->setTemplateOptions(
                ['area' => Area::AREA_FRONTEND, 'store' => $scopeId]
            );
            $this->transportBuilder->setTemplateIdentifier($emailTemplate);
            $this->transportBuilder->setFromByScope($emailSender);
            if ($order->getCustomerIsGuest()) {
                $customerName = $order->getBillingAddress()->getName();
            } else {
                $customerName = $order->getCustomerName();
            }

            $this->transportBuilder->addTo($emailAddress, $customerName);
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();

            $this->dataHelper->logger->logOrderEvent(
                $order,
                "Pay by Link email successfully sent."
            );

            return true;
        } catch (Exception $e) {
            $this->dataHelper->logger->logOrderEvent(
                $order,
                $e->getMessage()
            );
            return false;
        }
    }

    /**
     * @param $order
     * @param $paymentIntentId
     * @param $liveMode
     * @param $paymentMethodCode
     * @param $paymentLinkId
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function processPayByLinkPaymentCompleted(
        $order,
        $paymentIntentId,
        $liveMode,
        $paymentMethodCode,
        $paymentLinkId
    ): void
    {
        if (!$order->isCanceled() && !$order->hasInvoices()) {
            $paymentIntent = $this->brippoApiPaymentIntents->get($paymentIntentId, $liveMode);
            $currency = $paymentIntent[Stripe::PARAM_CURRENCY];
            $amount = $paymentIntent[Stripe::PARAM_AMOUNT] / 100;
            if ($paymentIntent[Stripe::PARAM_STATUS] == Stripe::PAYMENT_INTENT_STATUS_REQUIRES_CAPTURE) {
                $captureMethod = $this->dataHelper->getStoreConfig(
                    $paymentMethodCode === PayByLinkMethod::METHOD_CODE
                        ? PayByLinkMethod::XML_PATH_STORE_CONFIG_CAPTURE_METHOD
                        : PayByLinkMoto::XML_PATH_CONFIG_CAPTURE_METHOD,
                    $order->getStoreId()
                );
                $source = $paymentMethodCode === PayByLinkMethod::METHOD_CODE ? 'checkout' : 'backend';

                if ($captureMethod == CaptureMethodPayByLink::AUTOMATIC_CAPTURE) {
                    $this->captureAndInvoice($order, $paymentIntentId, $liveMode, $source, $amount, $currency);
                } else {
                    $this->processManualCapturePayment($order, $paymentIntent, $liveMode, $source);
                }

                $this->brippoApiPaymentLinks->cancel(
                    $paymentLinkId,
                    $liveMode
                );
                $this->dataHelper->logger->log("Payment Link " . $paymentLinkId . " successfully cancelled.");
            } else {
                $this->dataHelper->logger->log('Order #' . $order->getIncrementId() .
                    ' has invalid payment intent status: ' . $paymentIntent['status'] . '.');
            }
        } else {
            if ($order->isCanceled()) {
                $this->dataHelper->logger->log("Won't action as order #" . $order->getIncrementId() . ' is cancelled.');
            } elseif ($order->hasInvoices()) {
                $this->dataHelper->logger->log("Won't action as order #" .
                    $order->getIncrementId() . ' was already invoiced.');
            }
        }
    }

    /**
     * @param $order
     * @param $paymentIntent
     * @param $liveMode
     * @param $source
     * @return void
     * @throws Exception
     */
    private function processManualCapturePayment($order, $paymentIntent, $liveMode, $source)
    {
        if (isset($paymentIntent[Stripe::PARAM_LATEST_CHARGE])) {
            $cardData = $this->stripeHelper->getCardData($paymentIntent[Stripe::PARAM_LATEST_CHARGE]);
            $this->paymentsHelper->saveCardAdditionalData(
                $order->getPayment(),
                $cardData,
                $cardData ? $cardData['wallet']['type'] ?? null : null
            );
            $this->paymentsHelper->savePaymentAdditionalData(
                $order->getPayment(),
                $liveMode,
                $paymentIntent[Stripe::PARAM_ID],
                $source,
                $paymentIntent[Stripe::PARAM_STATUS],
                $paymentIntent[Stripe::PARAM_CURRENCY]
            );
        }

        $order->setState(Order::STATE_PENDING_PAYMENT)
            ->setStatus(BrippoOrder::STATUS_AUTHORIZED)
            ->save();
        $this->orderSender->send($order);
    }

    /**
     * @param $order
     * @param $paymentIntentId
     * @param $liveMode
     * @param $source
     * @param $amountToCapture
     * @param $currency
     * @return void
     * @throws LocalizedException
     */
    private function captureAndInvoice($order, $paymentIntentId, $liveMode, $source, $amountToCapture, $currency)
    {
        if ($order->canInvoice()) {
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

            $this->dataHelper->logger->log('Order #' . $order->getIncrementId() .
                ' successfully captured online.');

            if (isset($paymentIntent[Stripe::PARAM_LATEST_CHARGE])) {
                $cardData = $this->stripeHelper->getCardData($paymentIntent[Stripe::PARAM_LATEST_CHARGE]);
                $this->paymentsHelper->saveCardAdditionalData(
                    $order->getPayment(),
                    $cardData,
                    $cardData ? $cardData['wallet']['type'] ?? null : null
                );
                $this->paymentsHelper->savePaymentAdditionalData(
                    $order->getPayment(),
                    $liveMode,
                    $paymentIntentId,
                    $source,
                    $paymentIntent['status'],
                    $currency
                );
            }

            $this->paymentsHelper->invoiceOrder($order, $paymentIntentId);
        }
    }

    /**
     * @param OrderInterface $order
     * @param $scopeId
     * @return string
     * @throws Exception
     */
    protected function getPaymentHtml(OrderInterface $order, $scopeId)
    {
        return $this->magentoPaymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $scopeId
        );
    }

    /**
     * Render shipping address into html.
     *
     * @param OrderInterface $order
     * @return string|null
     */
    protected function getFormattedShippingAddress($order)
    {
        return $order->getIsVirtual()
            ? null
            : $this->addressRenderer->format($order->getShippingAddress(), 'html');
    }

    /**
     * Render billing address into html.
     *
     * @param OrderInterface $order
     * @return string|null
     */
    protected function getFormattedBillingAddress($order)
    {
        return $this->addressRenderer->format($order->getBillingAddress(), 'html');
    }

    /**
     * @param Quote $quote
     */
    public function fillMissingDataForPlaceOrder(Quote $quote)
    {
        if (empty($quote->getPayment()->getMethod())) {
            $quote->getPayment()->setMethod(PayByLinkMethod::METHOD_CODE);
        }
    }
}
