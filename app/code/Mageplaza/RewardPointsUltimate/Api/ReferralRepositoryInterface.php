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

namespace Mageplaza\RewardPointsUltimate\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Mageplaza\RewardPointsUltimate\Api\Data\ReferralInterface;
use Mageplaza\RewardPointsUltimate\Api\Data\ReferralSearchResultInterface;

/**
 * Interface ReferralRepositoryInterface
 * @package Mageplaza\RewardPointsPro\Api
 */
interface ReferralRepositoryInterface
{
    /**
     * Lists referral rule that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria The search criteria.
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\ReferralSearchResultInterface
     */
    public function getList(?\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null);

    /**
     * @param int $id
     *
     * @return bool
     */
    public function delete($id);

    /**
     * @param int $id
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\ReferralInterface
     */
    public function getRuleById($id);

    /**
     * @param \Mageplaza\RewardPointsUltimate\Api\Data\ReferralInterface $rule
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\ReferralInterface
     */
    public function save(\Mageplaza\RewardPointsUltimate\Api\Data\ReferralInterface $rule);
}
