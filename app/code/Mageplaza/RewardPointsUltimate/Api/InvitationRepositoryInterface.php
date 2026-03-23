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

namespace Mageplaza\RewardPointsUltimate\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Mageplaza\RewardPointsUltimate\Api\Data\InvitationSearchResultInterface;

/**
 * Interface InvitationRepositoryInterface
 * @api
 */
interface InvitationRepositoryInterface
{
    /**
     * Lists Invitation that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\InvitationSearchResultInterface Invitation search result
     *     interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $customerId
     * @param string $sendFrom
     * @param string $emails
     * @param string $message
     *
     * @return boolean
     */
    public function invite($customerId, $sendFrom, $emails, $message);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param string $email
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\InvitationSearchResultInterface
     */
    public function getReferralByEmail(SearchCriteriaInterface $searchCriteria, $email);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param int $customerId
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\InvitationSearchResultInterface
     */
    public function getReferralByCustomerId(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        $customerId
    );

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param string $email
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\InvitationSearchResultInterface
     */
    public function getInvitedByEmail(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria, $email);

    /**
     * @param string $code
     *
     * @return boolean
     */
    public function referByCode($code);

    /**
     * @param string $customerId
     *
     * @return string
     */
    public function getReferCode($customerId);

    /**
     * @return int
     */
    public function count();
}
