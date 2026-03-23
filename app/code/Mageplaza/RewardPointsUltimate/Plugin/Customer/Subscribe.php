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

namespace Mageplaza\RewardPointsUltimate\Plugin\Customer;

use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;

/**
 * Class Subscribe
 * @package Mageplaza\RewardPointsUltimate\Plugin\Customer
 */
class Subscribe
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Subscribe constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\Newsletter\Block\Subscribe $subject
     * @param $result
     *
     * @return string
     */
    public function afterToHtml(\Magento\Newsletter\Block\Subscribe $subject, $result)
    {
        $beforeClass  = $this->helperData->isEnabledHyvaTheme() ? '//div[@class="pr-4 lg:w-1/4 md:w-1/2"]' : '//div[@class="block newsletter"]';
        $pointContent = $this->helperData->getSubscribePointHtml(
            [
                'result' => $result,
                'query'  => $beforeClass
            ]
        );

        if ($pointContent) {
            return $pointContent;
        }

        return $result;
    }
}
