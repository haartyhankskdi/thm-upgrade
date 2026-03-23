<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
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

namespace Mageplaza\RewardPointsUltimate\Plugin\Order\Pdf\Items;

use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Helper\SellPoint;

/**
 * Class AbstractItems
 * @package Mageplaza\RewardPointsUltimate\Plugin\Order\Pdf\Items
 */
class AbstractItems
{
    /**
     * @var SellPoint
     */
    protected $helperData;

    /**
     * AbstractItems constructor.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\Sales\Model\Order\Pdf\Items\AbstractItems $subject
     * @param $result
     *
     * @return array
     * @throws LocalizedException
     */
    public function afterGetItemPricesForDisplay(\Magento\Sales\Model\Order\Pdf\Items\AbstractItems $subject, $result)
    {
        $item = $subject->getItem();
        $mpRewardSellPoints = $item->getMpRewardSellPoints();
        if ($mpRewardSellPoints > 0) {
            $pointHelper = $this->helperData->getPointHelper();

            return [
                [
                    'price' => $pointHelper->format($mpRewardSellPoints, false),
                    'subtotal' => $pointHelper->format(($mpRewardSellPoints * $item->getQty()), false),
                ],
            ];
        }

        return $result;
    }
}
