<?php
declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Plugin;

use Ebizmarts\SagePaySuite\Api\Data\ValidateRequestValueInterface as ValidateRequestInterface;
use Ebizmarts\SagePaySuite\Api\Data\ScaTransTypeInterface as TransactionType;
use Ebizmarts\SagePaySuite\Helper\BrowserData;
use Ebizmarts\SagePaySuite\Model;
use Ebizmarts\SagePaySuite\Model\CryptAndCodeData;
use Magento\Framework\UrlInterface;
use Magento\Framework\Exception\LocalizedException;

class StrongCustomerAuthRequestData
{
    private const CHALLENGE_WINDOW_SIZE_DEFAULT = 'Medium';
    private const NOTIFICATION_URL_CHAR_LIMIT = 256;

    /** @var \Ebizmarts\SagePaySuite\Model\Config */
    private $sagepayConfig;

    /** @var BrowserData */
    private $browserData;

    /** @var CryptAndCodeData */
    private $cryptAndCode;

    /** @var UrlInterface */
    private $coreUrl;

    /**
     * StrongCustomerAuthRequestData constructor.
     *
     * @param Model\Config $sagepayConfig
     * @param BrowserData $browserData
     * @param UrlInterface $coreUrl
     * @param CryptAndCodeData $cryptAndCode
     */
    public function __construct(
        Model\Config $sagepayConfig,
        BrowserData $browserData,
        UrlInterface $coreUrl,
        Model\CryptAndCodeData $cryptAndCode
    ) {
        $this->sagepayConfig = $sagepayConfig;
        $this->browserData   = $browserData;
        $this->coreUrl       = $coreUrl;
        $this->cryptAndCode  = $cryptAndCode;
    }

    /**
     * Exclude Pi remote javascript files from being minified.

     * @param \Ebizmarts\SagePaySuite\Model\PiRequest $subject
     * @param string[] $result
     * @return string[]
     */
    public function afterGetRequestData($subject, array $result) : array
    {
        /** @var \Ebizmarts\SagePaySuite\Api\Data\PiRequest $data */
        $data = $subject->getRequest();
        $quoteId = $subject->getCart()->getId();

        /** @var $subject \Ebizmarts\SagePaySuite\Model\PiRequest */
        $result['strongCustomerAuthentication'] = [
            'browserJavascriptEnabled' => 1,
            'browserJavaEnabled'       => $data->getJavaEnabled(),
            'browserColorDepth'        => $this->browserData->getBrowserColorDepth($data->getColorDepth()),
            'browserScreenHeight'      => $data->getScreenHeight(),
            'browserScreenWidth'       => $data->getScreenWidth(),
            'browserTZ'                => $data->getTimezone(),
            'browserAcceptHeader'      => $this->browserData->getHeaderAccept(),
            'browserIP'                => $this->browserData->getBrowserIP(),
            'browserLanguage'          => $this->browserData->getBrowserLanguage($data->getLanguage()),
            'browserUserAgent'         => $data->getUserAgent(),
            'notificationURL'          => $this->getNotificationUrl($quoteId, $data->getSaveToken()),
            'transType'                => TransactionType::GOOD_SERVICE_PURCHASE,
            'challengeWindowSize'      => $this->getChallengeWindowSize($result),
        ];

        if ($this->sagepayConfig->shouldAllowRepeatTransactions()) {
            $result['credentialType'] = [
                'cofUsage'      => $this->getCofUsage($data),
                'initiatedType' => 'CIT',
                'mitType'       => 'Unscheduled'
            ];
        }

        return $result;
    }

    /**
     * @param int $quoteId
     * @param bool $saveToken
     * @return string
     */
    private function getNotificationUrl($quoteId, $saveToken)
    {
        $encryptedQuoteId = $this->encryptAndEncode($quoteId);
        $params = [
            '_nosid' => true,
            '_secure' => true,
            '_store' => $this->sagepayConfig->getCurrentStoreId(),
            'quoteId' => $encryptedQuoteId,
            'saveToken' => $saveToken
        ];
        $url = $this->coreUrl->getUrl('elavon/pi/callback3Dv2', $params);

        if (strlen($url) > self::NOTIFICATION_URL_CHAR_LIMIT) {
            throw new LocalizedException(__(
                "Invalid url length (More than %1 characters), "
                . "Try shorten your admin url or contact your developers",
                self::NOTIFICATION_URL_CHAR_LIMIT
            ));
        }

        return $url;
    }

    /**
     * @param $data
     * @return string
     */
    private function encryptAndEncode($data)
    {
        return $this->cryptAndCode->encryptAndEncode($data);
    }

    /**
     * @param $data \Ebizmarts\SagePaySuite\Api\Data\PiRequest
     * @return string
     */
    private function getCofUsage($data)
    {
        return $data->getReusableToken() ? 'Subsequent' : 'First';
    }

    /**
     * @param array $result
     * @return string
     */
    private function getChallengeWindowSize($result)
    {
        return $this->isMotoMode($result)
            ? self::CHALLENGE_WINDOW_SIZE_DEFAULT
            : $this->sagepayConfig->getValue("challengewindowsize");
    }

    private function isMotoMode($result)
    {
        return (
            isset($result[ValidateRequestInterface::ENTRY_METHOD]) &&
            $result[ValidateRequestInterface::ENTRY_METHOD] === ValidateRequestInterface::ENTRY_METHOD_TELEPHONE_ORDER
        );
    }
}
