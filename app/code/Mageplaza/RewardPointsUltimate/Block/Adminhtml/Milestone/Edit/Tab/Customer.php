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

namespace Mageplaza\RewardPointsUltimate\Block\Adminhtml\Milestone\Edit\Tab;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as GroupFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\RewardPointsUltimate\Model\Milestone;

/**
 * Class Customer
 * @package Mageplaza\RewardPointsUltimate\Block\Adminhtml\Milestone\Edit\Tab
 */
class Customer extends Extended implements TabInterface
{
    /**
     * @var CollectionFactory
     */
    public $customerCollectionFactory;

    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     * @var GroupFactory
     */
    public $group;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * Product constructor.
     *
     * @param Data $backendHelper
     * @param CollectionFactory $customerCollectionFactory
     * @param Context $context
     * @param Registry $coreRegistry
     * @param GroupFactory $group
     * @param Config $eavConfig
     * @param array $data
     */
    public function __construct(
        Data $backendHelper,
        CollectionFactory $customerCollectionFactory,
        Context $context,
        Registry $coreRegistry,
        GroupFactory $group,
        Config $eavConfig,
        array $data = []
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->coreRegistry              = $coreRegistry;
        $this->group                     = $group;
        $this->eavConfig                 = $eavConfig;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set grid params
     */
    public function _construct()
    {
        parent::_construct();

        $this->setId('product_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        $id = $this->getParam('id');
        /** @var Collection $collection */
        $collection = $this->customerCollectionFactory->create();
        $collection->clear();

        $collection->getSelect()->joinLeft(
            ['mp_c' => $collection->getTable('mageplaza_reward_milestone_customer')],
            'e.entity_id = mp_c.customer_id',
            ['tier_id']
        );

        $collection->getSelect()->where('mp_c.tier_id = ?', $id);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', [
            'header'           => __('ID'),
            'sortable'         => true,
            'index'            => 'entity_id',
            'type'             => 'number',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
        ]);
        $this->addColumn('firstname', [
            'header'           => __('First Name'),
            'index'            => 'firstname',
            'header_css_class' => 'col-firstname',
            'column_css_class' => 'col-firstname',
        ]);
        $this->addColumn('lastname', [
            'header'           => __('Last Name'),
            'index'            => 'lastname',
            'header_css_class' => 'col-lastname',
            'column_css_class' => 'col-lastname',
        ]);
        $this->addColumn('email', [
            'header'           => __('Email'),
            'index'            => 'email',
            'header_css_class' => 'col-email',
            'column_css_class' => 'col-email'
        ]);
        $customerGroups = $this->getCustomerGroups();
        $this->addColumn('group_id', [
            'header'           => __('Group'),
            'index'            => 'group_id',
            'type'             => 'options',
            'options'          => $customerGroups,
            'header_css_class' => 'col-group',
            'column_css_class' => 'col-group'
        ]);
        $this->addColumn('gender', [
            'header'           => __('Gender'),
            'index'            => 'gender',
            'type'             => 'options',
            'options'          => $this->getCustomerGenderOptions(),
            'header_css_class' => 'col-gender',
            'column_css_class' => 'col-gender'
        ]);

        return $this;
    }

    /**
     * @return array
     */
    public function getCustomerGroups()
    {
        $groups         = [];
        $customerGroups = $this->group->create()
            ->addFieldToFilter('customer_group_id', ['neq' => 0])->toOptionArray();

        foreach ($customerGroups as $customerGroup) {
            $groups[$customerGroup['value']] = $customerGroup['label'];
        }

        return $groups;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getCustomerGenderOptions()
    {
        $attribute = $this->eavConfig->getAttribute('customer', 'gender');
        $options   = [];

        foreach ($attribute->getSource()->getAllOptions() as $id => $option) {
            $options[$id] = $option['label'];
        }

        return $options;
    }

    /**
     * Retrieve selected Tags
     *
     * @return array
     */
    public function getSelectedCustomer()
    {
        return [];
    }

    /**
     * @param Object $item
     *
     * @return string
     */
    public function getRowUrl($item)
    {
        return '#';
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/customerGrid', ['id' => $this->getTier()->getId()]);
    }

    /**
     * @return Milestone
     */
    public function getTier()
    {
        return $this->coreRegistry->registry('mageplaza_rw_milestone_tier');
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return __('Customer');
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('mprewardultimate/milestone/customer', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }
}
