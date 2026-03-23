<?php

namespace Ebizmarts\BrippoPayments\Controller\Adminhtml\Order;

use Ebizmarts\BrippoPayments\Helper\Data;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\RecoverCheckout;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;

class SendRecoverNotification extends Action
{
    protected $dataHelper;
    protected $logger;
    protected $jsonFactory;
    protected $recoverCheckoutHelper;
    protected $orderRepository;

    /**
     * @param Context $context
     * @param Logger $logger
     * @param Data $dataHelper
     * @param JsonFactory $jsonFactory
     * @param RecoverCheckout $recoverCheckoutHelper
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        Logger $logger,
        Data $dataHelper,
        JsonFactory  $jsonFactory,
        RecoverCheckout $recoverCheckoutHelper,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->jsonFactory = $jsonFactory;
        $this->recoverCheckoutHelper = $recoverCheckoutHelper;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        try {
            $requestBody = $this->dataHelper->unserializeRequestBody($this->getRequest());

            $orderId = isset($requestBody['orderId']) ? $requestBody['orderId'] : null;
            if (empty($orderId)) {
                throw new LocalizedException(__('Order ID not found'));
            }

            $order = $this->orderRepository->get($orderId);
            if (empty($order) || empty($order->getEntityId())) {
                throw new LocalizedException(__('Order not found'));
            }
            $this->logger->logOrderEvent(
                $order,
                'Sending manual recover order notification...'
            );

            $scopeId = $order->getStoreId();

            $sendEmail = isset($requestBody['sendEmail']) ? $requestBody['sendEmail'] : false;
            if ($sendEmail) {
                $customerEmail = isset($requestBody['customerEmail']) ? $requestBody['customerEmail'] : null;
                if (empty($customerEmail)) {
                    throw new LocalizedException(__('Customer email not found'));
                }

                $emailMessage = isset($requestBody['emailMessage']) ? $requestBody['emailMessage'] : null;
                if (empty($emailMessage)) {
                    throw new LocalizedException(__('Email message can not be empty'));
                }
            }

            $sendSMSWhatsApp = isset($requestBody['sendSMSWhatsApp']) ? $requestBody['sendSMSWhatsApp'] : false;
            if ($sendSMSWhatsApp) {
                $customerPhone = isset($requestBody['customerPhone']) ? $requestBody['customerPhone'] : null;
                if (empty($customerPhone)) {
                    throw new LocalizedException(__('Customer phone not found'));
                }

                $phoneMessage = isset($requestBody['phoneMessage']) ? $requestBody['phoneMessage'] : null;
                if (empty($phoneMessage)) {
                    throw new LocalizedException(__('SMS/WhatsApp message can not be empty'));
                }

                $messageType = isset($requestBody['messageType']) ? $requestBody['messageType'] : null;
                if (empty($messageType)) {
                    throw new LocalizedException(__('SMS/WhatsApp message type can not be empty'));
                }
            }

            if ($sendEmail) {
                try {
                    $this->logger->logOrderEvent(
                        $order,
                        'Sending email...'
                    );
                    $this->recoverCheckoutHelper->sendNotification(
                        $order,
                        $customerEmail,
                        $emailMessage,
                        true
                    );
                    $this->logger->logOrderEvent(
                        $order,
                        'Email sent!'
                    );
                } catch (Exception $ex) {
                    $this->logger->logOrderEvent(
                        $order,
                        $ex->getMessage()
                    );
                }
            } else {
                $this->logger->logOrderEvent(
                    $order,
                    'Notification via Email was not requested.'
                );
            }

            if ($sendSMSWhatsApp) {
                if ($messageType === 'sms' || $messageType === 'sms_whatsapp') {
                    try {
                        $this->logger->logOrderEvent(
                            $order,
                            'Sending SMS...'
                        );
                        $this->recoverCheckoutHelper->sendSMS(
                            $customerPhone,
                            $scopeId,
                            $phoneMessage . $this->recoverCheckoutHelper->getRecoverLink($order, RecoverCheckout::LINK_SOURCE_SMS, true)
                        );
                        $this->logger->logOrderEvent(
                            $order,
                            'SMS sent!'
                        );
                    } catch (Exception $ex) {
                        $this->logger->logOrderEvent(
                            $order,
                            $ex->getMessage()
                        );
                    }
                } else {
                    $this->logger->logOrderEvent(
                        $order,
                        'Notification via SMS was not requested.'
                    );
                }

                if ($messageType === 'whatsapp' || $messageType === 'sms_whatsapp') {
                    try {
                        $this->logger->logOrderEvent(
                            $order,
                            'Sending WhatsApp...'
                        );
                        $this->recoverCheckoutHelper->sendWhatsApp(
                            $customerPhone,
                            $scopeId,
                            $phoneMessage . $this->recoverCheckoutHelper->getRecoverLink($order, RecoverCheckout::LINK_SOURCE_WHATSAPP, true)
                        );
                        $this->logger->logOrderEvent(
                            $order,
                            'WhatsApp sent!'
                        );
                    } catch (Exception $ex) {
                        $this->logger->logOrderEvent(
                            $order,
                            $ex->getMessage()
                        );
                    }
                } else {
                    $this->logger->logOrderEvent(
                        $order,
                        'Notification via WhatsApp was not requested.'
                    );
                }
            } else {
                $this->logger->logOrderEvent(
                    $order,
                    'Notification via SMS/WhatsApp was not requested.'
                );
            }

            $response = [
                'valid' => 1
            ];
        } catch (Exception $ex) {
            $response = [
                'valid' => 0,
                'message' => $ex->getMessage()
            ];
            $this->logger->logOrderEvent(
                !empty($order) ? $order : null,
                $ex->getMessage()
            );
        }

        $resultJson = $this->jsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ebizmarts_BrippoPayments::recover_action');
    }
}
