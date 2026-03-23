<?php

namespace Ebizmarts\SagePaySuite\Plugin;

use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Ebizmarts\SagePaySuite\Model\Config;

class SameSiteCookies
{
    /** @var Config $config */
    private $config;

    /**
     * SwitchSameSite constructor.
     * @param Header $header
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param PhpCookieManager $subject
     * @param string $name
     * @param string $value
     * @param PublicCookieMetadata|null $metadata
     * @return array
     */
    public function beforeSetPublicCookie(
        PhpCookieManager $subject,
        $name,
        $value,
        PublicCookieMetadata $metadata = null
    ) {

        if ($this->isEnabled()) {
            $metadata->setSecure(true);
            $metadata->setSameSite('None');
        }

        return [$name, $value, $metadata];
    }

    private function isEnabled()
    {
        return $this->config->getAdvancedValue("same_site_cookies") == 1;
    }
}
