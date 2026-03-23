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

use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\RewardPointsPro\Model\Rules;
use Mageplaza\RewardPointsUltimate\Api\Data\ReferralExtensionInterface;
use Mageplaza\RewardPointsUltimate\Api\Data\ReferralInterface;

/**
 * Class Referral
 * @package Mageplaza\RewardPointsUltimate\Model
 */
class Referral extends Rules implements ReferralInterface
{
    const CACHE_TAG = 'mageplaza_rewardpoints_referral';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_rewardpoints_refer';

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_init(ResourceModel\Referral::class);
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
    public function afterSave()
    {
        $this->bindRuleToEntity($this->getResource(), 'referral_group_ids');
        parent::afterSave();
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        if (!$this->hasReferralGroupIds()) {
            $referralGroupIds = $this->_getResource()->getReferralGroupIds($this->getId());
            $this->setData(self::REFERRAL_GROUP_IDS, (array)$referralGroupIds);
        }
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getReferralRule()
    {
        $store = $this->storeManager->getStore();
        $rule = $this->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->setValidationFilter(
                $this->_customerSession->create()->getCustomerGroupId(),
                $store->getWebsiteId()
            )->getFirstItem();

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerAction()
    {
        return $this->getData(self::CUSTOMER_ACTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerAction($value)
    {
        return $this->setData(self::CUSTOMER_ACTION, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerPoints()
    {
        return $this->getData(self::CUSTOMER_POINTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerPoints($value)
    {
        return $this->setData(self::CUSTOMER_POINTS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerMoneyStep()
    {
        return $this->getData(self::CUSTOMER_MONEY_STEP);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerMoneyStep($value)
    {
        return $this->setData(self::CUSTOMER_MONEY_STEP, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerDiscount()
    {
        return $this->getData(self::CUSTOMER_DISCOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerDiscount($value)
    {
        return $this->setData(self::CUSTOMER_DISCOUNT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerApplyToShipping()
    {
        return $this->getData(self::CUSTOMER_APPLY_TO_SHIPPING);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerApplyToShipping($value)
    {
        return $this->setData(self::CUSTOMER_APPLY_TO_SHIPPING, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getReferralType()
    {
        return $this->getData(self::REFERRAL_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setReferralType($value)
    {
        return $this->setData(self::REFERRAL_TYPE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getReferralPoints()
    {
        return $this->getData(self::REFERRAL_POINTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setReferralPoints($value)
    {
        return $this->setData(self::REFERRAL_POINTS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getReferralMoneyStep()
    {
        return $this->getData(self::REFERRAL_MONEY_STEP);
    }

    /**
     * {@inheritdoc}
     */
    public function setReferralMoneyStep($value)
    {
        return $this->setData(self::REFERRAL_MONEY_STEP, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getReferralApplyToShipping()
    {
        return $this->getData(self::REFERRAL_APPLY_TO_SHIPPING);
    }

    /**
     * {@inheritdoc}
     */
    public function setReferralApplyToShipping($value)
    {
        return $this->setData(self::REFERRAL_APPLY_TO_SHIPPING, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getReferralGroupIds()
    {
        return $this->getData(self::REFERRAL_GROUP_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setReferralGroupIds($value)
    {
        return $this->setData(self::REFERRAL_GROUP_IDS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        ReferralExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
