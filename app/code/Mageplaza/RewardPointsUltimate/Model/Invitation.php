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

namespace Mageplaza\RewardPointsUltimate\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Mageplaza\RewardPointsUltimate\Api\Data\InvitationExtensionInterface;
use Mageplaza\RewardPointsUltimate\Api\Data\InvitationInterface;

/**
 * Class Invitation
 * @package Mageplaza\RewardPointsUltimate\Model
 */
class Invitation extends AbstractExtensibleModel implements IdentityInterface, InvitationInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'mageplaza_rewardpoints_invitation';

    /**
     * Prefix of model events names
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_rewardpoints_invitation';

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\Invitation::class);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * {@inheritdoc}
     */
    public function getInvitationId()
    {
        return $this->getData(self::INVITATION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setInvitationId($value)
    {
        return $this->setData(self::INVITATION_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getReferralEmail()
    {
        return $this->getData(self::REFERRAL_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setReferralEmail($value)
    {
        return $this->setData(self::REFERRAL_EMAIL, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getInvitedEmail()
    {
        return $this->getData(self::INVITED_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setInvitedEmail($value)
    {
        return $this->setData(self::INVITED_EMAIL, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getReferralEarn()
    {
        return $this->getData(self::REFERRAL_EARN);
    }

    /**
     * {@inheritdoc}
     */
    public function setReferralEarn($value)
    {
        return $this->setData(self::REFERRAL_EARN, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getInvitedEarn()
    {
        return $this->getData(self::INVITED_EARN);
    }

    /**
     * {@inheritdoc}
     */
    public function setInvitedEarn($value)
    {
        return $this->setData(self::INVITED_EARN, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getInvitedDiscount()
    {
        return $this->getData(self::INVITED_DISCOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setInvitedDiscount($value)
    {
        return $this->setData(self::INVITED_DISCOUNT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($value)
    {
        return $this->setData(self::STORE_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($value)
    {
        return $this->setData(self::CREATED_AT, $value);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return InvitationExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param InvitationExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        InvitationExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
