<?php

namespace Ebizmarts\BrippoPayments\Helper\PlatformService;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Magento\Backend\Model\UrlInterface as UrlBackend;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Magento\Framework\Url as UrlFrontend;
use Magento\Framework\Serialize\Serializer\Json;

abstract class PlatformService extends AbstractHelper
{
    const SERVICE_URL = 'https://dashboard.brippo.com/';
    const ENDPOINT_URI_ONBOARDING = 'accounts/onboarding/';
    const ENDPOINT_URI_WEBHOOK_MAPPING_CHECK = 'webhooks/check/';
    const ENDPOINT_URI_MONITOR = 'api/monitor/';
    const ENDPOINT_URI_ANALYTICS = 'api/public/v1/analytics/';
    const ENDPOINT_URI_SETTINGS_MONITOR = 'api/public/v1/monitor/settings/check';

    const PARAM_STORE_URL = 'store_url';
    const PARAM_ACCOUNT_ID = 'account_id';
    const PARAM_WEBHOOK_URL = 'webhook_url';
    const PARAM_LIVEMODE = 'livemode';
    const PARAM_HASH_KEY = 'hash_key';
    const PARAM_MESSAGE = 'message';
    const PARAM_ORDER_ID = 'order_id';
    const PARAM_PAYMENT_INTENT_ID = 'payment_intent_id';
    const PARAM_ORDER_INCREMENT_ID = 'order_increment_id';
    const PARAM_STRIPE_ACCOUNT_ID = 'stripeAccountId';
    const PARAM_LIVEMODE2 = 'liveMode';
    const PARAM_ENVIRONMENT = 'environment';
    const PARAM_EVENT_TYPE = 'eventType';
    const PARAM_DATE = 'date';
    const PARAM_STORE_URL2 = 'storeURL';
    const PARAM_EMAIL = 'email';
    const PARAM_DESCRIPTION = 'description';
    const PARAM_PAYMENT_INTENT_ID2 = 'paymentIntentId';
    const PARAM_RECEIPT_NUMBER = 'receiptNumber';

    protected $logger;
    protected $curl;
    protected $dataHelper;
    protected $urlBackend;
    protected $urlFrontend;
    protected $json;

    /**
     * @param Context $context
     * @param Logger $logger
     * @param Curl $curl
     * @param DataHelper $dataHelper
     * @param UrlFrontend $urlFrontend
     * @param UrlBackend $urlBackend
     * @param Json $json
     */
    public function __construct(
        Context $context,
        Logger $logger,
        Curl $curl,
        DataHelper $dataHelper,
        UrlFrontend $urlFrontend,
        UrlBackend $urlBackend,
        Json $json
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->curl = $curl;
        $this->dataHelper = $dataHelper;
        $this->urlFrontend = $urlFrontend;
        $this->urlBackend = $urlBackend;
        $this->json = $json;
    }

    /**
     * @throws LocalizedException
     */
    protected function curlPostRequest(string $serviceUrl, array $payload)
    {
        $this->curl->post($serviceUrl, $payload);

        if ($this->curl->getStatus() != 200) {
            $this->logger->log($serviceUrl . ' STATUS CODE: ' . $this->curl->getStatus());
            // phpcs:disable
            $this->logger->log(print_r($this->curl->getBody(), true));
            // phpcs:enable
            throw new LocalizedException(
                __('Invalid Platform Service response status ' . $this->curl->getStatus() . '.')
            );
        }

        $response = $this->json->unserialize($this->curl->getBody());

        if (empty($response)
            || ((!isset($response['valid']) || !$response['valid'])
                && !isset($response['ok']))) {
            // phpcs:disable
            $this->logger->log(print_r($response, true));
            // phpcs:enable
            if (!empty($response) && isset($response['message'])) {
                throw new LocalizedException(__($response['message']));
            } else {
                throw new LocalizedException(
                    __('Invalid Platform Service response.')
                );
            }
        }

        return $response;
    }
}
