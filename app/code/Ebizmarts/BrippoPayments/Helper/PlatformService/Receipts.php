<?php

namespace Ebizmarts\BrippoPayments\Helper\PlatformService;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

class Receipts extends PlatformService
{
    const ENDPOINT_SEND_RECEIPT = 'api/app/receipt/';
    const ENDPOINT_RECEIPT_PDF = 'api/app/receipt-pdf/';

    /**
     * @param string $scope
     * @param int $scopeId
     * @param string $paymentIntentId
     * @param string $receiptNumber
     * @param string $description
     * @return string|null
     * @throws LocalizedException
     */
    public function getReceiptPdfUrl(
        string $scope,
        int $scopeId,
        string $paymentIntentId,
        string $receiptNumber,
        string $description
    ): ?string
    {
        $liveMode = $this->dataHelper->isLiveMode($scopeId, $scope);
        $params = [
            self::PARAM_PAYMENT_INTENT_ID2 => $paymentIntentId,
            self::PARAM_LIVEMODE2 => $liveMode,
            self::PARAM_STRIPE_ACCOUNT_ID => $this->dataHelper->getAccountId($scopeId, $liveMode, $scope),
            self::PARAM_DESCRIPTION => $description,
            self::PARAM_RECEIPT_NUMBER => $receiptNumber
        ];

        $response = $this->curlPostRequest(
            self::SERVICE_URL . self::ENDPOINT_RECEIPT_PDF,
            $params
        );

        $this->dataHelper->logger->log(print_r($response, true));

        return isset($response['url']) ? $response['url'] : '';
    }

    /**
     * @param string $scope
     * @param int $scopeId
     * @param string $paymentIntentId
     * @param string $email
     * @param string $receiptNumber
     * @param string $description
     * @return void
     * @throws LocalizedException
     */
    public function sendReceipt(
        string $scope,
        int $scopeId,
        string $paymentIntentId,
        string $email,
        string $receiptNumber,
        string $description
    ): void
    {
        $liveMode = $this->dataHelper->isLiveMode($scopeId, $scope);
        $params = [
            self::PARAM_PAYMENT_INTENT_ID2 => $paymentIntentId,
            self::PARAM_LIVEMODE2 => $liveMode,
            self::PARAM_STRIPE_ACCOUNT_ID => $this->dataHelper->getAccountId($scopeId, $liveMode, $scope),
            self::PARAM_EMAIL => $email,
            self::PARAM_DESCRIPTION => $description,
            self::PARAM_RECEIPT_NUMBER => $receiptNumber
        ];

        $this->curlPostRequest(
            self::SERVICE_URL . self::ENDPOINT_SEND_RECEIPT,
            $params
        );
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function getReceiptDescription(OrderInterface $order): string
    {
        return 'Order #' . $order->getIncrementId() . ' from ' . $this->dataHelper->getStoreDomain();
    }
}
