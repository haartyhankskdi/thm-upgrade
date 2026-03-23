<?php

namespace Ebizmarts\BrippoPayments\Helper;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\SendMessage;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Magento\Store\Model\StoreManager;

class RecoverCheckout extends AbstractHelper
{
    const CONTROLLER_URL_RECOVER_CHECKOUT       = 'brippo_payments/payments/recover';
    const XML_PATH_RECOVER_CHECKOUT_ACTIVE      = 'brippo_payments/recover_checkout/active';
    const XML_PATH_RECOVER_AUTO_NOTIFICATIONS   = 'brippo_payments/recover_checkout/automatic_notifications';
    const XML_PATH_RECOVER_AUTO_NOTIF_FIRST     = 'brippo_payments/recover_checkout/automatic_notifications_first';
    const XML_PATH_RECOVER_AUTO_NOTIF_SPACING   = 'brippo_payments/recover_checkout/automatic_notifications_separation';
    const XML_PATH_RECOVER_AUTO_NOTIF_TEMPLATE  = 'brippo_payments/recover_checkout/automatic_notifications_template';
    const XML_PATH_RECOVER_AUTO_NOTIF_SENDER    = 'brippo_payments/recover_checkout/automatic_notifications_sender';
    const RECOVER_CHECKOUT_AUTOMATIC_NOTIF_DEFAULT_TEMPLATE_ID = 'brippo_recover_checkout_automatic_notification';
    const RECOVER_CHECKOUT_EMAIL_MESSAGE_DEFAULT = "Dear [name],\n\rYour order from [store] is awaiting payment.\n"
        . "Please click on to following link to finalize payment:";

    const RECOVER_CHECKOUT_SMS_MESSAGE_DEFAULT = "Your order from [store] is awaiting payment. "
        . "Please go to:";
    const CONFIG_PATH_SEND_EMAIL                 = 'brippo_payments/recover_checkout/automatic_notifications_email';
    const CONFIG_PATH_SEND_SMS                   = 'brippo_payments/recover_checkout/automatic_notifications_sms';
    const CONFIG_PATH_SEND_WHATSAPP              = 'brippo_payments/recover_checkout/automatic_notifications_whatsapp';

    const LINK_SOURCE_EMAIL                      = 'email';
    const LINK_SOURCE_SMS                        = 'sms';
    const LINK_SOURCE_WHATSAPP                   = 'whatsapp';
    const LINK_SOURCE_MANUAL                     = 'manual';
    const RECOVER_HASH                           = '76fsd68f-fsd8-fddf7-V8';

    public $logger;

    /**
     * @var DataHelper
     */
    protected $dataHelper;
    protected $addressRenderer;
    protected $transportBuilder;
    protected $urlBuilder;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    protected $storeManager;
    protected $brippoApiSendMessage;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param Context $context
     * @param Logger $logger
     * @param Data $dataHelper
     * @param AddressRenderer $addressRenderer
     * @param TransportBuilder $transportBuilder
     * @param UrlInterface $urlBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManager $storeManager
     * @param SendMessage $brippoApiSendMessage
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context                     $context,
        Logger                      $logger,
        DataHelper                  $dataHelper,
        AddressRenderer             $addressRenderer,
        TransportBuilder            $transportBuilder,
        UrlInterface                $urlBuilder,
        OrderRepositoryInterface    $orderRepository,
        SearchCriteriaBuilder       $searchCriteriaBuilder,
        StoreManager                $storeManager,
        SendMessage                 $brippoApiSendMessage,
        EncryptorInterface          $encryptor
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->addressRenderer = $addressRenderer;
        $this->transportBuilder = $transportBuilder;
        $this->urlBuilder = $urlBuilder;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->brippoApiSendMessage = $brippoApiSendMessage;
        $this->encryptor = $encryptor;
    }

    /**
     * @param OrderInterface $order
     * @param string $customerEmail
     * @param string $message
     * @param bool $isManual
     * @return void
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendNotification(
        OrderInterface $order,
        string $customerEmail,
        string $message,
        bool $isManual = false
    ): void
    {
        $emailTemplate = $this->dataHelper->getStoreConfig(
            self::XML_PATH_RECOVER_AUTO_NOTIF_TEMPLATE,
            $order->getStoreId()
        );
        $legacyTemplatePattern = '/^payment_.*_brippo_payments_recover_checkout_automatic_notifications_template$/';
        if (empty($emailTemplate) || preg_match($legacyTemplatePattern, $emailTemplate)) {
            $emailTemplate = self::RECOVER_CHECKOUT_AUTOMATIC_NOTIF_DEFAULT_TEMPLATE_ID;
        }

        $emailSender = $this->dataHelper->getStoreConfig(
            self::XML_PATH_RECOVER_AUTO_NOTIF_SENDER,
            $order->getStoreId()
        );

        $message = str_replace("\n", "<br>", $message);
        $recoverLink = $this->getRecoverLink($order, self::LINK_SOURCE_EMAIL, $isManual);

        $transport = [
            'order' => $order,
            'order_id' => $order->getId(),
            'billing' => $order->getBillingAddress(),
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
            'payment_link' => $recoverLink,
            'message' => $message
        ];

        $transportObject = new DataObject($transport);

        $this->transportBuilder->setTemplateVars($transportObject->getData());
        $this->transportBuilder->setTemplateOptions(
            ['area' => Area::AREA_FRONTEND, 'store' => $order->getStoreId()]
        );
        $this->transportBuilder->setTemplateIdentifier($emailTemplate);
        $this->transportBuilder->setFromByScope($emailSender);
        if ($order->getCustomerIsGuest()) {
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $customerName = $order->getCustomerName();
        }

        $this->transportBuilder->addTo($customerEmail, $customerName);
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();

        $this->dataHelper->logger->logOrderEvent(
            $order,
            'Sent recover email with recover link ' . $recoverLink
        );

        $brippoRecoverTries = 0;
        if ($order->getPayment()
            && !empty($order->getPayment()->getAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_RECOVER_TRIES
            ))) {
            $brippoRecoverTries = intval($order->getPayment()->getAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_RECOVER_TRIES
            ));
        }
        $order->getPayment()->setAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_RECOVER_TRIES,
            $brippoRecoverTries+1
        )->save();
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function wasCanceledOrderRecovered(OrderInterface $order): bool
    {
        /*
         * QUALIFIES AS RECOVER IF SAME EMAIL, GRAND TOTAL, NEWER IN TIME AND ANY SUCCESS STATE
         */
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('customer_email', $order->getCustomerEmail())
            ->addFilter('grand_total', $order->getGrandTotal())
            ->addFilter('created_at', $order->getCreatedAt(), 'gteq')
            ->addFilter('state', [
                Order::STATE_PENDING_PAYMENT,
                Order::STATE_CLOSED,
                Order::STATE_COMPLETE,
                Order::STATE_PROCESSING
            ], 'in')
            ->create();
        $orders = $this->orderRepository->getList($searchCriteria);
        return $orders->getTotalCount() > 0;
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
     * @param OrderInterface $order
     * @return string
     * @throws NoSuchEntityException
     */
    public function getEmailDefaultMessage(OrderInterface $order):string
    {
        $customerName = $order->getCustomerFirstname();
        $storeName = $this->storeManager->getStore($order->getStoreId())->getName();
        return str_replace("[store]", $storeName, str_replace("[name]", $customerName, self::RECOVER_CHECKOUT_EMAIL_MESSAGE_DEFAULT));
    }

    /**
     * @param OrderInterface $order
     * @return string
     * @throws NoSuchEntityException
     */
    public function getSMSMessage(OrderInterface $order): string {
        $link = $this->getRecoverLink($order, self::LINK_SOURCE_SMS);
        $storeName = $this->storeManager->getStore($order->getStoreId())->getName();
        return "Your order from $storeName is awaiting payment. Please go to $link";
    }

    /**
     * @param OrderInterface $order
     * @param string $source
     * @param bool $isManual
     * @return string
     */
    public function getRecoverLink(OrderInterface $order, string $source, bool $isManual = false): string {
        $orderId = $order->getIncrementId();
        $signature = hash_hmac('sha256', $orderId, $this->encryptor->getHash(self::RECOVER_HASH));

        return $order->getStore()->getBaseUrl()
            . self::CONTROLLER_URL_RECOVER_CHECKOUT
            . '/order/'  . $orderId
            . '/source/' . $source
            . '/sig/'    . $signature
            . ($isManual ? '/manual/1' : '');
    }

    /**
     * @param OrderInterface $order
     * @return string
     * @throws NoSuchEntityException
     */
    public function getWhatsAppMessage(OrderInterface $order): string {
        $link = $this->getRecoverLink($order, self::LINK_SOURCE_WHATSAPP);
        $storeName = $this->storeManager->getStore($order->getStoreId())->getName();
        return "Your order from $storeName is awaiting payment. Please go to $link";
    }

    /**
     * @param $customerPhone
     * @param $scopeId
     * @param $message
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function sendSMS($customerPhone, $scopeId, $message): void
    {
        $liveMode = $this->dataHelper->isLiveMode($scopeId);
        $this->brippoApiSendMessage->sendSMS(
            $liveMode,
            $this->dataHelper->getAccountId($scopeId, $liveMode),
            $this->dataHelper->getStoreConfig(
                DataHelper::CONFIG_PATH_BRIPPO_API_KEY,
                $scopeId
            ),
            $customerPhone,
            $message
        );
    }

    /**
     * @param $customerPhone
     * @param $scopeId
     * @param $message
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function sendWhatsApp($customerPhone, $scopeId, $message) {
        $liveMode = $this->dataHelper->isLiveMode($scopeId);
        $this->brippoApiSendMessage->sendWhatsApp(
            $liveMode,
            $this->dataHelper->getAccountId($scopeId, $liveMode),
            $this->dataHelper->getStoreConfig(
                DataHelper::CONFIG_PATH_BRIPPO_API_KEY,
                $scopeId
            ),
            $customerPhone,
            $message
        );
    }

    /**
     * @param $linkSource
     * @param $isManual
     * @param $originalPaymentMethod
     * @param $isSoftFailRecovery
     * @param $notificationNumber
     * @return array
     */
    public function getRecoverCheckoutMetadata($linkSource, $isManual, $originalPaymentMethod, $isSoftFailRecovery, $notificationNumber): array
    {
        $recoverMetadata = [
            Stripe::METADATA_KEY_RECOVER_SOURCE => $linkSource,
            Stripe::METADATA_KEY_RECOVERED_FROM_PAYMENT_METHOD => $originalPaymentMethod
        ];
        if ($isManual) {
            $recoverMetadata[Stripe::METADATA_KEY_RECOVER_MANUAL] = true;
        }

        if ($isSoftFailRecovery) {
            $recoverMetadata[Stripe::METADATA_KEY_IS_SOFT_FAIL_RECOVERY] = true;
        }
        if ($notificationNumber >= 0) {
            $recoverMetadata[Stripe::METADATA_KEY_NOTIFICATION_NUMBER] = $notificationNumber;
        }
        return $recoverMetadata;
    }
}
