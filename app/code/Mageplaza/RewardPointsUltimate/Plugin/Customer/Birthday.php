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

use Magento\Customer\Block\Widget\Dob;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;

/**
 * Class Birthday
 * @package Mageplaza\RewardPointsUltimate\Plugin\Customer
 */
class Birthday
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Birthday constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param Dob $subject
     * @param $result
     *
     * @return string
     */
    public function afterToHtml(Dob $subject, $result)
    {
        $pointContent = $this->helperData->getPointHtml(
            CustomerEvents::CUSTOMER_BIRTHDAY,
            [
                'result' => $result,
                'query' => '//div[@class="control customer-dob"]'
            ]
        );

        if ($pointContent) {
            return $pointContent;
        }

        return $result;
    }
}
