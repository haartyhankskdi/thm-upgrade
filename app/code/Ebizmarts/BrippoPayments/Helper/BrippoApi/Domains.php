<?php

namespace Ebizmarts\BrippoPayments\Helper\BrippoApi;

use Magento\Framework\Exception\LocalizedException;

class Domains extends Service
{
    const API_URI = 'payment-method-domain/';

    /**
     * @param bool $liveMode
     * @param string $domainName
     * @return array
     * @throws LocalizedException
     */
    public function registerAndValidate(bool $liveMode, string $domainName): array
    {
        $serviceUrl = self::SERVICE_URL . 'v1/' .$this->getMode($liveMode) . '/' . self::API_URI;
        $params = [
            "domain_name" => $domainName
        ];

        return $this->curlPostRequest($serviceUrl, $params);
    }
}
