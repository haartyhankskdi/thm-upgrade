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
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Mageplaza\RewardPointsPro\Api\Data\SCEarningRuleInterface;
use Mageplaza\RewardPointsPro\Api\Data\SCEarningSearchResultInterface;

/**
 * Interface SCEarningRuleRepositoryInterface
 * @package Mageplaza\RewardPointsPro\Api
 */
interface SCEarningRuleRepositoryInterface
{
    /**
     * Lists shopping cart earning rule that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria The search criteria.
     *
     * @return \Mageplaza\RewardPointsPro\Api\Data\SCEarningSearchResultInterface Shopping cart earning rule search
     * result interface.
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
     * @return \Mageplaza\RewardPointsPro\Api\Data\SCEarningRuleInterface
     */
    public function getEarningRuleById($id);

    /**
     * @param \Mageplaza\RewardPointsPro\Api\Data\SCEarningRuleInterface $rule
     *
     * @return \Mageplaza\RewardPointsPro\Api\Data\SCEarningRuleInterface
     */
    public function save(\Mageplaza\RewardPointsPro\Api\Data\SCEarningRuleInterface $rule);
}
