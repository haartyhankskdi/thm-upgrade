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

namespace Mageplaza\RewardPointsUltimate\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface ReferralSearchResultInterface
 * @package Mageplaza\RewardPointsUltimate\Api\Data
 */
interface ReferralSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get items.
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\ReferralInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Set items.
     *
     * @param \Mageplaza\RewardPointsUltimate\Api\Data\ReferralInterface[] $items
     *
     * @return $this
     */
    public function setItems(?array $items = null);
}
