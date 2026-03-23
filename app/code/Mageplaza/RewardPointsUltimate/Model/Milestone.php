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

use Exception;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Mageplaza\RewardPointsUltimate\Model\Source\Status;

/**
 * Class Milestone
 * @package Mageplaza\RewardPointsUltimate\Model
 * @method getCustomerGroupIds()
 */
class Milestone extends AbstractExtensibleModel implements IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'mageplaza_reward_milestone';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_reward_milestone';

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\Milestone::class);
    }

    /**
     * Milestone constructor.
     *
     * @param MessageManagerInterface $messageManager
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        MessageManagerInterface $messageManager,
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->messageManager = $messageManager;
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
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return AbstractExtensibleModel
     */
    protected function _afterLoad()
    {
        if (!$this->hasData('customer_group_id')) {
            $this->setData('customer_group_id', explode(',', $this->getCustomerGroupIds() ?: ''));
        }

        return parent::_afterLoad();
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getCustomerIds()
    {
        if (!$this->hasData('customer_ids')) {
            $ids = $this->_getResource()->getCustomerIds($this);
            $this->setData('customer_ids', $ids);
        }

        return (array) $this->_getData('customer_ids');
    }

    /**
     * @param int $customerId
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function loadByCustomerId($customerId)
    {
        $this->_getResource()->loadByCustomerId($this, $customerId);

        return $this;
    }

    /**
     * @param int $orderTotal
     * @param int $groupId
     * @param int $websiteId
     *
     * @return Milestone|DataObject
     */
    public function loadUpTier($orderTotal, $groupId, $websiteId)
    {
        $collection = $this->getCollection();

        $collection->addFieldToFilter('status', ['eq' => Status::ENABLE])
            ->addFieldToFilter('sum_order', ['lteq' => $orderTotal])
            ->addFieldToFilter('website_ids', ['finset' => $websiteId])
            ->addFieldToFilter('customer_group_ids', ['finset' => $groupId]);
        $collectionClone = clone $collection;
        if ($this->getId()) {
            $collection->addFieldToFilter('min_point', ['gt' => $this->getMinPoint()]);
        }
        $collection->setOrder('min_point', Collection::SORT_ORDER_ASC);
        $upTier   = $collection->getFirstItem();
        $minPoint = $upTier->getMinPoint();
        $collectionClone->addFieldToFilter('min_point', $minPoint);
        if ($collectionClone->getSize() > 1) {
            return $collectionClone->getLastItem();
        }

        return $collection->getFirstItem();
    }

    /**
     * @param int $customerId
     *
     * @return bool
     */
    public function upTier($customerId)
    {
        try {
            $this->getResource()->upTier($this, $customerId);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param int $customerId
     *
     * @return bool
     */
    public function addTier($customerId)
    {
        try {
            $this->getResource()->addTier($this, $customerId);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param int $customerId
     *
     * @return bool
     */
    public function deleteTier($customerId)
    {
        try {
            $this->getResource()->deleteTier($this, $customerId);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function checkDuplicatePoint()
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('website_ids', ['eq' => $this->getData('website_ids')])
            ->addFieldToFilter('min_point', ['eq' => $this->getData('min_point')]);

        if ($this->getId()) {
            $collection->addFieldToFilter('tier_id', ['neq' => $this->getData('tier_id')]);
        }

        return (bool) $collection->count();
    }

    /**
     * @param string $groupId
     *
     * @return bool
     */
    public function checkDuplicateGroup($groupId)
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter('customer_group_ids', ['finset' => $groupId])
            ->addFieldToFilter('website_ids', ['eq' => $this->getData('website_ids')])
            ->addFieldToFilter('min_point', ['eq' => $this->getData('min_point')]);

        if ($this->getId()) {
            $collection->addFieldToFilter('tier_id', ['neq' => $this->getData('tier_id')]);
        }

        return (bool) $collection->count();
    }
}
