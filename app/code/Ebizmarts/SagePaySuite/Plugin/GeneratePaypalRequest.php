<?php

namespace Ebizmarts\SagePaySuite\Plugin;

use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\UrlInterface;
use Ebizmarts\SagePaySuite\Model\PayPalRequestManagement;
use Magento\Quote\Model\Quote;
use Ebizmarts\SagePaySuite\Api\Data\PayPalRequest;
use Ebizmarts\SagePaySuite\Helper\BrowserData;
use Magento\Framework\Exception\LocalizedException;

class GeneratePaypalRequest
{
    private const NOTIFICATION_URL_CHAR_LIMIT = 255;

    /** @var Config */
    private $sagepayConfig;

    /** @var UrlInterface */
    private $coreUrl;

    /** @var Quote */
    private $quote;

    /** @var EncryptorInterface */
    private $encryptor;

    /** @var BrowserData */
    private $browserData;

    /**
     * GeneratePaypalRequest constructor.
     *
     * @param Config $sagepayConfig
     * @param BrowserData $browserData
     * @param UrlInterface $coreUrl
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Config $sagepayConfig,
        BrowserData $browserData,
        UrlInterface $coreUrl,
        EncryptorInterface $encryptor
    ) {
        $this->sagepayConfig = $sagepayConfig;
        $this->browserData   = $browserData;
        $this->coreUrl       = $coreUrl;
        $this->encryptor     = $encryptor;
    }

    /**
     * @param PayPalRequestManagement $subject
     * @param string[] $result
     * @return string[]
     */
    public function afterGenerateRequest($subject, array $result) : array
    {
        /** @var Quote quote */
        $this->quote = $subject->getCart();
        /** @var PayPalRequest $paypalRequest */
        $paypalRequest = $subject->getPayPalRequestData();
        $result['ClientIPAddress'] = $this->browserData->getBrowserIP();
        $result['ThreeDSNotificationURL'] = $this->getCallbackUrl();
        $result['BrowserAcceptHeader'] = $this->browserData->getHeaderAccept();
        $result['BrowserUserAgent'] = $paypalRequest->getUserAgent();
        $result['BrowserLanguage'] =  $this->browserData->getBrowserLanguage($paypalRequest->getLanguage());
        $result['ChallengeWindowSize'] = sprintf("%02d", 1);
        $result['BrowserJavascriptEnabled'] = $paypalRequest->getJavascriptEnabled();
        $result['BrowserJavaEnabled'] = $paypalRequest->getJavaEnabled();
        $result['BrowserColorDepth'] = $this->browserData->getBrowserColorDepth($paypalRequest->getColorDepth());
        $result['BrowserScreenHeight'] = $paypalRequest->getScreenHeight();
        $result['BrowserScreenWidth'] = $paypalRequest->getScreenWidth();
        $result['BrowserTZ'] = $paypalRequest->getTimezone();

        return $result;
    }

    /**
     * @return string
     */
    private function getCallbackUrl()
    {
        $url = $this->coreUrl->getUrl('elavon/paypal/callback', [
            '_nosid' => true,
            '_secure' => true,
            '_store'  => $this->quote->getStoreId()
        ]);

        $url .= "?quoteid=" . urlencode($this->encryptor->encrypt($this->quote->getId()));

        if (strlen($url) > self::NOTIFICATION_URL_CHAR_LIMIT) {
            $message = "Invalid url length (More than %1 characters), Try shorten your".
            "admin url or contact your developers";
            
            throw new LocalizedException(__($message, self::NOTIFICATION_URL_CHAR_LIMIT));
        }

        return $url;
    }
}
