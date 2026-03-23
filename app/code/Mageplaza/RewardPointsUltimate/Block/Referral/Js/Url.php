<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Block\Referral\Js;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\RewardPointsUltimate\Helper\Cookie;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\Source\UrlParam;

/**
 * Class Url
 * @package Mageplaza\RewardPointsUltimate\Block\Referral\Js
 */
class Url extends Template
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Url constructor.
     *
     * @param Context $context
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|string
     */
    public function getReferralConfig()
    {
        if (!$this->helperData->isEnabled()) {
            return false;
        }

        $isUrlParam = $this->helperData->getURLParam() == UrlParam::URL_PARAM;
        $urlPrefix = $this->helperData->getURLPrefix();
        $defaultUrl = $this->helperData->getDefaultReferUrl();
        if ($isUrlParam && empty($urlPrefix)) {
            $urlPrefix = Data::DEFAULT_URL_PREFIX;
        }
        $referralConfig['cookieName'] = Cookie::MP_REFER;
        $referralConfig['prefix'] = $urlPrefix;
        $referralConfig['isUrlParam'] = $isUrlParam;
        $referralConfig['defaultReferUrl'] = $defaultUrl ? $this->getUrl($defaultUrl) : false;

        return Data::jsonEncode($referralConfig);
    }
}
