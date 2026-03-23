<?php

namespace Ebizmarts\BrippoPayments\Helper;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\SendMessage;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\StoreManager;

class SoftFailRecover extends AbstractHelper
{
    const CONFIG_PATH_SOFT_FAIL_RECOVERY              = 'brippo_payments/advanced/soft_fail_recovery_active';
    const CONFIG_PATH_SOFT_FAIL_RECOVERY_CODES        = 'brippo_payments/advanced/soft_fail_recovery_allowed_codes';
    const BRIPPO_FAILED_TRYING_TO_RECOVER_TEMPLATE_ID   = 'brippo_failed_trying_to_recover';
    const CONFIG_PATH_EMAIL_TEMPLATE                    = 'brippo_payments/advanced/soft_fail_recovery_email_template';
    const CONFIG_PATH_EMAIL_SENDER                      = 'brippo_payments/advanced/soft_fail_recovery_email_sender';
    const CONFIG_PATH_ALLOW_SECOND_ATTEMPT              = 'brippo_payments/advanced/soft_fail_recovery_only_second';
    const CONFIG_PATH_EXPIRATION_HOURS                  = 'brippo_payments/advanced/soft_fail_recovery_expiration_hours';
    const CONFIG_PATH_NOTIFICATIONS                     = 'brippo_payments/advanced/soft_fail_recovery_notifications';
    const SOFT_FAIL_RECOVER_ERROR_TYPE_3DS              = '3ds_errors';
    const SOFT_FAIL_RECOVER_ERROR_TYPE_GENERIC          = 'generic_errors';
    const SOFT_FAIL_RECOVER_ERROR_TYPE_INSUFFICIENT_FUNDS = 'insufficient_funds_errors';
    const SOFT_FAIL_RECOVER_ERROR_TYPE_INCORRECT_CVC    = 'incorrect_cvc_errors';
    const SOFT_FAIL_RECOVER_ERROR_TYPE_EXPIRED_CARD    = 'expired_card_errors';


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
     * @param Context $context
     * @param Data $dataHelper
     * @param AddressRenderer $addressRenderer
     * @param TransportBuilder $transportBuilder
     * @param UrlInterface $urlBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManager $storeManager
     * @param SendMessage $brippoApiSendMessage
     */
    public function __construct(
        Context                     $context,
        DataHelper                  $dataHelper,
        AddressRenderer             $addressRenderer,
        TransportBuilder            $transportBuilder,
        UrlInterface                $urlBuilder,
        OrderRepositoryInterface    $orderRepository,
        SearchCriteriaBuilder       $searchCriteriaBuilder,
        StoreManager                $storeManager,
        SendMessage                 $brippoApiSendMessage
    ) {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->addressRenderer = $addressRenderer;
        $this->transportBuilder = $transportBuilder;
        $this->urlBuilder = $urlBuilder;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->brippoApiSendMessage = $brippoApiSendMessage;
    }

    /**
     * @param OrderInterface $order
     * @param string $customerEmail
     * @param int $notificationNumber
     * @return void
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendNotification(
        OrderInterface $order,
        string $customerEmail,
        int $notificationNumber
    ): void
    {
        $emailTemplate = $this->dataHelper->getStoreConfig(
            self::CONFIG_PATH_EMAIL_TEMPLATE,
            $order->getStoreId()
        );
        $legacyTemplatePattern = '/^payment_.*_brippo_payments_soft_fail_recovery_soft_fail_recovery_email_template$/';
        if (empty($emailTemplate) || preg_match($legacyTemplatePattern, $emailTemplate)) {
            $emailTemplate = self::BRIPPO_FAILED_TRYING_TO_RECOVER_TEMPLATE_ID;
        }

        $emailSender = $this->dataHelper->getStoreConfig(
            self::CONFIG_PATH_EMAIL_SENDER,
            $order->getStoreId()
        );

        $link = $order->getStore()->getBaseUrl()
            . RecoverCheckout::CONTROLLER_URL_RECOVER_CHECKOUT
            . '/order/' . $order->getIncrementId() . '/source/email'
            . '/softf/1/notif/' . $notificationNumber
        ;

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
            'payment_link' => $link
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

//        $brippoRecoverTries = 0;
//        if ($order->getPayment()
//            && !empty($order->getPayment()->getAdditionalInformation(
//                PaymentMethod::ADDITIONAL_DATA_RECOVER_TRIES
//            ))) {
//            $brippoRecoverTries = intval($order->getPayment()->getAdditionalInformation(
//                PaymentMethod::ADDITIONAL_DATA_RECOVER_TRIES
//            ));
//        }
//        $order->getPayment()->setAdditionalInformation(
//            PaymentMethod::ADDITIONAL_DATA_RECOVER_TRIES,
//            $brippoRecoverTries+1
//        )->save();
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

//    /**
//     * @param OrderInterface $order
//     * @return string
//     * @throws NoSuchEntityException
//     */
//    public function getSMSMessage(OrderInterface $order): string {
//        $link = $this->getSMSRecoverLink($order);
//        $storeName = $this->storeManager->getStore($order->getStoreId())->getName();
//        return "Your order from $storeName is awaiting payment. Please go to $link";
//    }
//
//    /**
//     * @param OrderInterface $order
//     * @param bool $isManual
//     * @return string
//     */
//    public function getSMSRecoverLink(OrderInterface $order, bool $isManual = false): string {
//        return $order->getStore()->getBaseUrl()
//            . RecoverCheckout::CONTROLLER_URL_RECOVER_CHECKOUT
//            . '/order/' . $order->getIncrementId() . '/source/sms'
//            . ($isManual ? '/manual/1' : '') ;
//    }
//
//    /**
//     * @param OrderInterface $order
//     * @return string
//     * @throws NoSuchEntityException
//     */
//    public function getWhatsAppMessage(OrderInterface $order): string {
//        $link = $this->getWhatsAppRecoverLink($order);
//        $storeName = $this->storeManager->getStore($order->getStoreId())->getName();
//        return "Your order from $storeName is awaiting payment. Please go to $link";
//    }
//
//    /**
//     * @param OrderInterface $order
//     * @param bool $isManual
//     * @return string
//     */
//    public function getWhatsAppRecoverLink(OrderInterface $order, bool $isManual = false): string {
//        return $order->getStore()->getBaseUrl()
//            . RecoverCheckout::CONTROLLER_URL_RECOVER_CHECKOUT
//            . '/order/' . $order->getIncrementId() . '/source/whatsapp'
//            . ($isManual ? '/manual/1' : '') ;
//    }
//
//    /**
//     * @param $customerPhone
//     * @param $scopeId
//     * @param $message
//     * @return void
//     * @throws LocalizedException
//     * @throws NoSuchEntityException
//     */
//    public function sendSMS($customerPhone, $scopeId, $message) {
//        $liveMode = $this->dataHelper->isLiveMode($scopeId);
//        $this->brippoApiSendMessage->sendSMS(
//            $liveMode,
//            $this->dataHelper->getAccountId($scopeId, $liveMode),
//            $this->dataHelper->getStoreConfig(
//                DataHelper::CONFIG_PATH_BRIPPO_API_KEY,
//                $scopeId
//            ),
//            $customerPhone,
//            $message
//        );
//    }
//
//    /**
//     * @param $customerPhone
//     * @param $scopeId
//     * @param $message
//     * @return void
//     * @throws LocalizedException
//     * @throws NoSuchEntityException
//     */
//    public function sendWhatsApp($customerPhone, $scopeId, $message) {
//        $liveMode = $this->dataHelper->isLiveMode($scopeId);
//        $this->brippoApiSendMessage->sendWhatsApp(
//            $liveMode,
//            $this->dataHelper->getAccountId($scopeId, $liveMode),
//            $this->dataHelper->getStoreConfig(
//                DataHelper::CONFIG_PATH_BRIPPO_API_KEY,
//                $scopeId
//            ),
//            $customerPhone,
//            $message
//        );
//    }

    /**
     * @param $scopeId
     * @return array
     */
    public function getAllowedErrorCodes($scopeId):array {
        $errorCodes = [];

        try {
            $allowedErrorTypes = $this->dataHelper->getStoreConfig(
                self::CONFIG_PATH_SOFT_FAIL_RECOVERY_CODES,
                $scopeId
            );

            if (!empty($allowedErrorTypes)) {
                if (strpos($allowedErrorTypes, self::SOFT_FAIL_RECOVER_ERROR_TYPE_INCORRECT_CVC) !== false) {
                    $errorCodes []= Stripe::CONFIRM_ERROR_CODE_INCORRECT_CVC;
                }
                if (strpos($allowedErrorTypes, self::SOFT_FAIL_RECOVER_ERROR_TYPE_EXPIRED_CARD) !== false) {
                    $errorCodes []= Stripe::CONFIRM_ERROR_CODE_EXPIRED_CARD;
                }
                if (strpos($allowedErrorTypes, self::SOFT_FAIL_RECOVER_ERROR_TYPE_3DS) !== false) {
                    $errorCodes []= Stripe::CONFIRM_ERROR_CODE_PAYMENT_INTENT_AUTHENTICATION_FAILURE;
                }
                if (strpos($allowedErrorTypes, self::SOFT_FAIL_RECOVER_ERROR_TYPE_INSUFFICIENT_FUNDS) !== false) {
                    $errorCodes []= Stripe::CONFIRM_ERROR_CODE_INSUFFICIENT_FUNDS;
                }
                if (strpos($allowedErrorTypes, self::SOFT_FAIL_RECOVER_ERROR_TYPE_GENERIC) !== false) {
                    $errorCodes []= Stripe::CONFIRM_ERROR_CODE_PROCESSING_ERROR;
                    $errorCodes []= Stripe::CONFIRM_ERROR_CODE_CARD_VELOCITY_EXCEEDED;
                    $errorCodes []= Stripe::CONFIRM_ERROR_CODE_DO_NOT_HONOR;
                    $errorCodes []= Stripe::CONFIRM_ERROR_CODE_INVALID_AMOUNT;
                    $errorCodes []= Stripe::CONFIRM_ERROR_CODE_TRANSACTION_NOT_ALLOWED;
                    $errorCodes []= Stripe::CONFIRM_ERROR_CODE_TRY_AGAIN_LATER;
                    $errorCodes []= Stripe::CONFIRM_ERROR_CODE_CARD_DECLINED;
                }
            }
        } catch (Exception $ex) {
            $this->dataHelper->logger->log($ex->getMessage());
            $this->dataHelper->logger->log($ex->getTraceAsString());
        }

        return $errorCodes;
    }
}
