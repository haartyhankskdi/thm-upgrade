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

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Phrase;

/**
 * Class Benefit
 * @package Mageplaza\RewardPointsUltimate\Block\Adminhtml\Milestone\Edit\Tab
 */
class Benefit extends Generic implements TabInterface
{
    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('mageplaza_rw_milestone_tier');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('tier_');
        $form->setFieldNameSuffix('tier');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Milestone Tier Benefit')]);

        $fieldset->addField('earn_percent', 'text', [
            'label' => __('Increase Earning Points'),
            'title' => __('Increase Earning Points'),
            'class' => 'validate-digits',
            'note' => __('Apply for Earning Rate, Catalog Rules and Shopping Cart Rules'),
            'name' => 'earn_percent',
        ]);

        $fieldset->addField('earn_fixed', 'text', [
            'label' => __('Fixed Points'),
            'title' => __('Fixed Points'),
            'class' => 'validate-digits',
            'note' => __('Apply for Behavior Rules'),
            'name' => 'earn_fixed',
        ]);

        $fieldset->addField('spent_percent', 'text', [
            'label' => __('Decrease Spending Points'),
            'title' => __('Decrease Spending Points'),
            'class' => 'validate-digits',
            'name' => 'spent_percent',
        ]);

        if ($model) {
            $form->setValues($model->getData());
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Benefit');
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
}
