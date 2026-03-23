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

namespace Mageplaza\RewardPointsUltimate\Block\Adminhtml\Milestone\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Website;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerGroupFactory;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mageplaza\Core\Helper\Media;
use Mageplaza\RewardPointsUltimate\Block\Adminhtml\Renderer\Image;
use Mageplaza\RewardPointsUltimate\Model\Milestone;
use Mageplaza\RewardPointsUltimate\Model\Source\Status;

/**
 * Class General
 * @package Mageplaza\RewardPointsUltimate\Block\Adminhtml\Earning\Behavior\Edit\Tab
 */
class General extends Generic implements TabInterface
{
    /**
     * @var Status
     */
    protected $_tierStatus;

    /**
     * @var Website
     */
    protected $_websites;

    /**
     * @var CustomerGroupFactory
     */
    protected $_customerGroupsFactory;

    /**
     * @var Media
     */
    protected $mediaHelper;

    /**
     * General constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Status $status
     * @param Media $mediaHelper
     * @param Website $websites
     * @param CustomerGroupFactory $customerGroupsFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Status $status,
        Media $mediaHelper,
        Website $websites,
        CustomerGroupFactory $customerGroupsFactory,
        array $data = []
    ) {
        $this->_tierStatus = $status;
        $this->_websites = $websites;
        $this->_customerGroupsFactory = $customerGroupsFactory;
        $this->mediaHelper = $mediaHelper;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        /** @var Milestone $tier */
        $tier = $this->_coreRegistry->registry('mageplaza_rw_milestone_tier');
        /** @var Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('tier_');
        $form->setFieldNameSuffix('tier');

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('General'),
            'class' => 'fieldset-wide'
        ]);

        $fieldset->addField('name', 'text', [
            'name' => 'name',
            'label' => __('Tier Name'),
            'title' => __('Tier Name'),
            'required' => true
        ]);

        $fieldset->addField('status', 'select', [
            'name' => 'status',
            'label' => __('Status'),
            'title' => __('Status'),
            'values' => $this->_tierStatus->toOptionArray(),
            'disabled' => $this->isDisable($tier)
        ]);

        $fieldset->addField('image', Image::class, [
            'name' => 'image',
            'label' => __('Image'),
            'title' => __('Image'),
            'path' => $this->mediaHelper->getBaseMediaPath('rewardpoints/tier'),
            'note' => __('The appropriate size is 265px * 250px.')
        ]);

        $fieldset->addField('customer_group_id', 'multiselect', [
            'name' => 'customer_group_id',
            'title' => __('Customer Group(s)'),
            'required' => true,
            'label' => __('Customer Group(s)'),
            'values' => $this->getCustomerGroup(),
            'disabled' => $this->isDisable($tier)
        ]);

        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField('website_ids', 'multiselect', [
                'name' => 'website_ids',
                'title' => __('Website'),
                'label' => __('Website'),
                'required' => true,
                'values' => $this->_websites->toOptionArray(),
                'disabled' => $this->isDisable($tier)
            ]);
        }

        $fieldset->addField('min_point', 'text', [
            'name' => 'min_point',
            'label' => __('Min Points Value'),
            'title' => __('Min Points Value'),
            'required' => true,
            'class' => 'validate-number integer validate-greater-than-zero',
            'disabled' => $this->isDisable($tier)
        ]);

        $fieldset->addField('sum_order', 'text', [
            'name' => 'sum_order',
            'label' => __('Number of Orders'),
            'title' => __('Number of Orders'),
            'class' => 'validate-number integer',
            'disabled' => $this->isDisable($tier)
        ]);

        $fieldset->addField('description', 'textarea', [
            'name' => 'description',
            'label' => __('Description'),
            'title' => __('Description')
        ]);

        if ($tier) {
            $form->addValues($tier->getData());
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param Milestone $tier
     *
     * @return bool
     */
    public function isDisable(Milestone $tier)
    {
        return $tier->getId() === '1';
    }

    /**
     * Get customer group
     * @return mixed
     */
    public function getCustomerGroup()
    {
        $customerGroups = $this->_customerGroupsFactory->create()->toOptionArray();
        if (isset($customerGroups[0])) {
            unset($customerGroups[0]);
        }

        return $customerGroups;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
