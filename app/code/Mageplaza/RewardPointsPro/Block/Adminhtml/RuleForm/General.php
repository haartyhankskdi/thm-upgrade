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
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Block\Adminhtml\RuleForm;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Website;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerGroupFactory;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;

/**
 * Class General
 * @package Mageplaza\RewardPointsPro\Block\Adminhtml\RuleForm
 */
abstract class General extends Generic implements TabInterface
{
    /**
     * @var Website
     */
    protected $_websites;

    /**
     * @var CustomerGroupFactory
     */
    protected $_customerGroupsFactory;

    /**
     * @var string
     */
    protected $_modelRegistry = 'catalog_earning_rule';

    /**
     * General constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Website $websites
     * @param CustomerGroupFactory $customerGroupsFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Website $websites,
        CustomerGroupFactory $customerGroupsFactory,
        array $data = []
    ) {
        $this->_websites              = $websites;
        $this->_customerGroupsFactory = $customerGroupsFactory;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id'     => 'edit_form',
                    'action' => $this->getUrl('*/*/save', ['_current' => true]),
                    'method' => 'post',
                ],
            ]
        );

        $model = $this->_coreRegistry->registry($this->_modelRegistry);
        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Rule Information')]);
        $fieldset->addField('is_apply', 'hidden', ['name' => 'is_apply',]);

        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id',]);
        }
        $fieldset->addField('name', 'text', [
            'name'     => 'name',
            'label'    => __('Rule name'),
            'title'    => __('Rule name'),
            'required' => true,
        ]);
        $fieldset->addField('description', 'textarea', [
            'label' => __('Description'),
            'title' => __('Description'),
            'name'  => 'description',
            'cols'  => 20,
            'rows'  => 5,
            'value' => '',
            'wrap'  => 'soft',
        ]);
        $fieldset->addField('is_active', 'select', [
            'label'  => __('Status'),
            'title'  => __('Status'),
            'name'   => 'is_active',
            'values' => [
                [
                    'value' => '0',
                    'label' => __('Inactive'),
                ],
                [
                    'value' => '1',
                    'label' => __('Active'),
                ]
            ],
            'value'  => 0
        ]);
        $fieldset->addField('website_ids', 'multiselect', [
            'name'     => 'website_ids',
            'title'    => __('Website'),
            'label'    => __('Website'),
            'required' => true,
            'values'   => $this->_websites->toOptionArray()
        ]);
        $fieldset->addField('customer_group_ids', 'multiselect', [
            'name'     => 'customer_group_ids',
            'title'    => __('Customer Group(s)'),
            'required' => true,
            'label'    => __('Customer Group(s)'),
            'values'   => $this->getCustomerGroups(true)
        ]);
        $fieldset->addField('from_date', 'date', [
            'name'        => 'from_date',
            'label'       => __('From'),
            'date_format' => 'yyyy-MM-dd',
        ]);
        $fieldset->addField('to_date', 'date', [
            'name'        => 'to_date',
            'label'       => __('To'),
            'date_format' => 'yyyy-MM-dd',
        ]);
        $fieldset->addField('sort_order', 'text', [
            'name'  => 'sort_order',
            'label' => __('Priority'),
            'title' => __('Priority'),
            'class' => 'validate-number',
        ]);

        $this->addExtraFieldset($form, $fieldset);

        if ($model->getRuleId()) {
            $form->setValues($model->getData());
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param Form $form
     * @param null $fieldset
     *
     * @return $this
     */
    public function addExtraFieldset($form, $fieldset = null)
    {
        return $this;
    }

    /**
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Rule Information');
    }

    /**
     * @return Phrase
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
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isDisplayCustomerGroupNotLogin($includeNotLogin = false)
    {
        return $includeNotLogin;
    }

    /**
     * @return array
     */
    public function getCustomerGroups($includeNotLogin = false)
    {
        $customerGroups = $this->_customerGroupsFactory->create()->toOptionArray();
        if (!$this->isDisplayCustomerGroupNotLogin($includeNotLogin)) {
            if (isset($customerGroups[0])) {
                unset($customerGroups[0]);
            }
        }

        return $customerGroups;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        $html = parent::toHtml();

        $html .= '<script type="text/x-magento-init">{"*":{"Mageplaza_RewardPointsPro/js/validate":{}}}</script>';

        return $html;
    }
}
