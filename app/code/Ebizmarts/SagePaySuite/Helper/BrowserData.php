<?php

namespace Ebizmarts\SagePaySuite\Helper;

use Magento\Framework\HTTP\PhpEnvironment\Request;

class BrowserData
{
    private const BROWSER_LANGUAGE_MAX_LENGTH = 8;
    private const START_SUBSTRING_OFFSET = 0;
    private const END_SUBSTRING_LENGTH = 5;

    /** @var Request */
    private $request;

    /**
     * BrowserData constructor.
     *
     * @param Request $request
     */
    public function __construct(
        Request $request
    ) {
        $this->request = $request;
    }

    /**
     * @return bool|string
     */
    public function getHeaderAccept()
    {
        return $this->request->getHeader('Accept');
    }

    /**
     * @param int $colorDepth
     * @return int
     */
    public function getBrowserColorDepth($colorDepth)
    {
        return $colorDepth == 30 ? 24 : $colorDepth;
    }

    /**
     * @param string $browserLanguage
     * @return string
     */
    public function getBrowserLanguage($browserLanguage)
    {
        $browserLanguage = $browserLanguage === null ? '' : $browserLanguage;
        if (strlen($browserLanguage) > self::BROWSER_LANGUAGE_MAX_LENGTH) {
            $browserLanguage = substr($browserLanguage, self::START_SUBSTRING_OFFSET, self::END_SUBSTRING_LENGTH);
        }
        return $browserLanguage;
    }

    /**
     * @return mixed|string
     */
    public function getBrowserIP()
    {
        $browserIP = '127.0.0.1';
        $clientIp = $this->request->getClientIp();
        if (!empty($clientIp)) {
            $ipAddressesArray = explode(',', $clientIp);

            foreach ($ipAddressesArray as $ipAddress) {
                $ipAddress = trim($ipAddress);

                if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4|FILTER_FLAG_IPV6)) {
                    $browserIP = $ipAddress;
                    break;
                }
            }
        }
        return $browserIP;
    }
}
