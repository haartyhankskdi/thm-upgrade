<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Block\Widget;

use Amasty\GdprCookie\Model\ConfigProvider;
use Amasty\GdprCookie\Model\Cookie;
use Amasty\GdprCookie\Model\CookieGroup;
use Amasty\GdprCookie\Model\CookiePolicy;
use Amasty\GdprCookie\Model\Cookie\CookieData;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Settings extends Template implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_GdprCookie::widget/settings.phtml';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CookieData
     */
    private $cookieData;

    /**
     * @var CookiePolicy
     */
    private $cookiePolicy;

    public function __construct(
        ConfigProvider $configProvider,
        CookieData $cookieData,
        Template\Context $context,
        CookiePolicy $cookiePolicy,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->cookieData = $cookieData;
        $this->cookiePolicy = $cookiePolicy;
    }

    /**
     * @return array
     */
    public function getGroupData()
    {
        $storeId = $this->_storeManager->getStore()->getId();

        return $this->cookieData->getGroupData($storeId);
    }

    public function isNeedToShow(): bool
    {
        return $this->cookiePolicy->isCookiePolicyAllowed();
    }

    public function getCacheLifetime()
    {
        return null;
    }
}
