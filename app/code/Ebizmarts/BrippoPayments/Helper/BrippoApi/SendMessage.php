<?php

namespace Ebizmarts\BrippoPayments\Helper\BrippoApi;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Magento\Framework\Exception\LocalizedException;

class SendMessage extends Service
{
    const API_URI = 'send-message/';

    /**
     * @param $liveMode
     * @param $accountId
     * @param $apiKey
     * @param $phone
     * @param $message
     * @return array
     * @throws LocalizedException
     */
    public function sendSMS($liveMode, $accountId, $apiKey, $phone, $message): array
    {
        $serviceUrl = self::SERVICE_URL . 'v1/'
            . self::API_URI . 'sms';

        $payload = [
            "to" => $phone,
            "message" => $message
        ];

        return $this->curlPostRequest(
            $serviceUrl,
            $payload,
            $this->getAuthHeaders(
                $liveMode,
                $accountId,
                $apiKey
            ));
    }

    /**
     * @param $liveMode
     * @param $accountId
     * @param $apiKey
     * @param $phone
     * @param $message
     * @return array
     * @throws LocalizedException
     */
    public function sendWhatsApp($liveMode, $accountId, $apiKey, $phone, $message): array
    {
        $serviceUrl = self::SERVICE_URL . 'v1/'
            . self::API_URI . 'whatsapp';

        $payload = [
            "to" => $phone,
            "message" => $message
        ];

        return $this->curlPostRequest(
            $serviceUrl,
            $payload,
            $this->getAuthHeaders(
                $liveMode,
                $accountId,
                $apiKey
            ));
    }
}
