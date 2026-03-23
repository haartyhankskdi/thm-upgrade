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

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPoints\Model\ResourceModel\Transaction\Collection;
use Mageplaza\RewardPointsUltimate\Api\Data\BehaviorExtensionInterface;
use Mageplaza\RewardPointsUltimate\Api\Data\BehaviorInterface;

/**
 * Class Behavior
 * @package Mageplaza\RewardPointsUltimate\Model
 */
class Behavior extends AbstractExtensibleModel implements IdentityInterface, BehaviorInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'mageplaza_rewardpoints_behavior';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_rewardpoints_behavior';

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Collection
     */
    protected $transactionCollection;

    /**
     * @var TimezoneInterface
     */
    protected $date;

    /**
     * Behavior constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param StoreManagerInterface $storeManager
     * @param HttpContext $httpContext
     * @param TimezoneInterface $date
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        StoreManagerInterface $storeManager,
        HttpContext $httpContext,
        TimezoneInterface $date,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->httpContext   = $httpContext;
        $this->_storeManager = $storeManager;
        $this->date          = $date;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\Behavior::class);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param $resource
     * @param $field
     */
    public function bindRuleToEntity($resource, $field)
    {
        $data = $this->getData($field);
        if ($data) {
            if (!is_array($data)) {
                $data = explode(',', (string) $data);
            }
            $resource->bindRuleToEntity($this->getRuleId(), $data, substr($field, 0, -4));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        $this->bindRuleToEntity($this->getResource(), 'website_ids');
        $this->bindRuleToEntity($this->getResource(), 'customer_group_ids');
        parent::afterSave();
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        if (!$this->hasCustomerGroupIds()) {
            $customerGroupIds = $this->_getResource()->getCustomerGroupIds($this->getId());
            $this->setData('customer_group_ids', (array) $customerGroupIds);
        }
        if (!$this->hasWebsiteIds()) {
            $websiteIds = $this->_getResource()->getWebsiteIds($this->getId());
            $this->setData('website_ids', (array) $websiteIds);
        }

        parent::_afterLoad();
    }

    /**
     * @param $action
     * @param bool $isFilterCustomerGroup
     * @param string $customerGroup
     *
     * @return int
     */
    public function getPointByAction($action, $isFilterCustomerGroup = false, $customerGroup = '')
    {
        $behavior = $this->getBehaviorRuleByAction($action, $isFilterCustomerGroup, $customerGroup);
        if ($behavior->getRuleId()) {
            return $behavior->getPointAmount();
        }

        return 0;
    }

    /**
     * @param $action
     * @param bool $isFilterCustomerGroup
     * @param string $customerGroup
     *
     * @return DataObject
     */
    public function getBehaviorRuleByAction($action, $isFilterCustomerGroup = false, $customerGroup = '')
    {
        $now       = $this->date->date()->format('Y-m-d');
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        if (!$websiteId) {
            $websiteId = $this->getCustomerWebsiteId();
        }
        $collection = $this->getCollection()
            ->addFieldToFilter('point_action', $action)
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('from_date', [['null' => true], ['lteq' => $now]])
            ->addFieldToFilter('to_date', [['null' => true], ['gteq' => $now]])
            ->addFieldToFilter('website_ids', $websiteId);

        if ($isFilterCustomerGroup) {
            if (!$customerGroup) {
                $contextGroup  = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP);
                $customerGroup = $contextGroup ?: 0;
            }
            $collection->addFieldToFilter('customer_group_ids', $customerGroup);
        }
        $collection->setOrder('sort_order', 'ASC');

        $behaviorRule = $collection->getFirstItem();

        $this->_eventManager->dispatch('mpreward_before_earning_points', [
            'rule'        => $behaviorRule,
            'customer_id' => null,
            'type'        => 'earn_behavior'
        ]);

        return $behaviorRule;
    }

    /**
     * @param $action
     * @param $customerId
     *
     * @return mixed
     */
    public function checkMaxPoint($action, $customerId)
    {
        return $this->getResource()->checkMaxPoint($action, $this, $customerId);
    }

    /**
     * @param $customerId
     *
     * @return mixed
     */
    public function checkCustomerHasBirthday($customerId)
    {
        return $this->getResource()->checkCustomerHasBirthday($customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getRuleId()
    {
        return $this->getData(self::RULE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setRuleId($value)
    {
        return $this->setData(self::RULE_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($value)
    {
        return $this->setData(self::NAME, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($value)
    {
        return $this->setData(self::DESCRIPTION, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getFromDate()
    {
        return $this->getData(self::FROM_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setFromDate($value)
    {
        return $this->setData(self::FROM_DATE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getToDate()
    {
        return $this->getData(self::TO_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setToDate($value)
    {
        return $this->setData(self::TO_DATE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($value)
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function setSortOrder($value)
    {
        return $this->setData(self::SORT_ORDER, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPointAction()
    {
        return $this->getData(self::POINT_ACTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setPointAction($value)
    {
        return $this->setData(self::POINT_ACTION, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMinWords()
    {
        return $this->getData(self::MIN_WORDS);
    }

    /**
     * {@inheritdoc}
     */
    public function getMinGrandTotal()
    {
        return $this->getData(self::MIN_GRAND_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function getMinDays()
    {
        return $this->getData(self::MIN_DAYS);
    }

    /**
     * {@inheritdoc}
     */
    public function setMinWords($value)
    {
        return $this->setData(self::MIN_WORDS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function setMinDays($value)
    {
        return $this->setData(self::MIN_DAYS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsPurchased()
    {
        return $this->getData(self::IS_PURCHASED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsPurchased($value)
    {
        return $this->setData(self::IS_PURCHASED, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsLoop()
    {
        return $this->getData(self::IS_LOOP);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsLoop($value)
    {
        return $this->setData(self::IS_LOOP, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsEnabledEmail()
    {
        return $this->getData(self::IS_ENABLED_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsEnabledEmail($value)
    {
        return $this->setData(self::IS_ENABLED_EMAIL, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getSender()
    {
        return $this->getData(self::SENDER);
    }

    /**
     * {@inheritdoc}
     */
    public function setSender($value)
    {
        return $this->setData(self::SENDER, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailTemplate()
    {
        return $this->getData(self::EMAIL_TEMPLATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailTemplate($value)
    {
        return $this->setData(self::EMAIL_TEMPLATE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMinInterval()
    {
        return $this->getData(self::MIN_INTERVAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setMinInterval($value)
    {
        return $this->setData(self::MIN_INTERVAL, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getAction()
    {
        return $this->getData(self::ACTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setAction($value)
    {
        return $this->setData(self::ACTION, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getFbAppId()
    {
        return $this->getData(self::FB_APP_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setFbAppId($value)
    {
        return $this->setData(self::FB_APP_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPointAmount()
    {
        return $this->getData(self::POINT_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setPointAmount($value)
    {
        return $this->setData(self::POINT_AMOUNT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxPoint()
    {
        return $this->getData(self::MAX_POINT);
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxPoint($value)
    {
        return $this->setData(self::MAX_POINT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxPointPeriod()
    {
        return $this->getData(self::MAX_POINT_PERIOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxPointPeriod($value)
    {
        return $this->setData(self::MAX_POINT_PERIOD, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function setWebsiteIds(array $value)
    {
        return $this->setData(self::WEBSITE_IDS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteIds()
    {
        return $this->getData(self::WEBSITE_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroupIds()
    {
        return $this->getData(self::CUSTOMER_GROUP_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerGroupIds(array $value)
    {
        return $this->setData(self::CUSTOMER_GROUP_IDS, $value);
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
        BehaviorExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * @param string $action
     *
     * @return string|null
     */
    public function getExpireAfterByAction(string $action): ?string
    {
        $behavior = $this->getBehaviorRuleByAction($action);
        if (!$behavior->getId()) {
            return null;
        }

        $expireAfter = $behavior->getData('expire_after');
        if ($expireAfter) {
            return $expireAfter;
        }

        return null;
    }
}
