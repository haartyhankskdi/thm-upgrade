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

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface InvitationInterface
 * @package Mageplaza\RewardPointsUltimate\Api\Data
 */
interface InvitationInterface extends ExtensibleDataInterface
{
    const INVITATION_ID    = 'invitation_id';
    const REFERRAL_EMAIL   = 'referral_email';
    const INVITED_EMAIL    = 'invited_email';
    const REFERRAL_EARN    = 'referral_earn';
    const INVITED_EARN     = 'invited_earn';
    const INVITED_DISCOUNT = 'invited_discount';
    const STORE_ID         = 'store_id';
    const CREATED_AT       = 'created_at';

    /**
     * @return int
     */
    public function getInvitationId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setInvitationId($value);

    /**
     * @return string
     */
    public function getReferralEmail();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setReferralEmail($value);

    /**
     * @return string
     */
    public function getInvitedEmail();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setInvitedEmail($value);

    /**
     * @return int
     */
    public function getReferralEarn();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setReferralEarn($value);

    /**
     * @return int
     */
    public function getInvitedEarn();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setInvitedEarn($value);

    /**
     * @return float
     */
    public function getInvitedDiscount();

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setInvitedDiscount($value);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setStoreId($value);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCreatedAt($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\InvitationExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Mageplaza\RewardPointsUltimate\Api\Data\InvitationExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        \Mageplaza\RewardPointsUltimate\Api\Data\InvitationExtensionInterface $extensionAttributes
    );
}
