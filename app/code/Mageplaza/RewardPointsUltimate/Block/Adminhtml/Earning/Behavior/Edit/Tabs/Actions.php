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

namespace Mageplaza\RewardPointsUltimate\Block\Adminhtml\Earning\Behavior\Edit\Tabs;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Mageplaza\RewardPointsUltimate\Model\Config\Source\DaysOfMonth;
use Mageplaza\RewardPointsUltimate\Model\Config\Source\DaysOfWeek;
use Mageplaza\RewardPointsUltimate\Model\Config\Source\Frequency;
use Mageplaza\RewardPointsUltimate\Model\Config\Source\MonthsOfYear;
use Mageplaza\RewardPointsUltimate\Model\Source\PointActions;
use Mageplaza\RewardPointsUltimate\Model\Source\PointPeriod;

/**
 * Class Actions
 * @package Mageplaza\RewardPointsUltimate\Block\Adminhtml\Earning\Behavior\Edit\Tabs
 */
class Actions extends Generic implements TabInterface
{
    /**
     * @var Frequency
     */
    protected $frequency;

    /**
     * @var DaysOfWeek
     */
    protected $daysOfWeek;

    /**
     * @var DaysOfMonth
     */
    protected $daysOfMonth;

    /**
     * @var MonthsOfYear
     */
    protected $monthsOfYear;

    /**
     * Actions constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Frequency $frequency
     * @param DaysOfWeek $daysOfWeek
     * @param DaysOfMonth $daysOfMonth
     * @param MonthsOfYear $monthsOfYear
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Frequency $frequency,
        DaysOfWeek $daysOfWeek,
        DaysOfMonth $daysOfMonth,
        MonthsOfYear $monthsOfYear,
        array $data = []
    ) {
        $this->frequency    = $frequency;
        $this->daysOfWeek   = $daysOfWeek;
        $this->daysOfMonth  = $daysOfMonth;
        $this->monthsOfYear = $monthsOfYear;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('behavior_earning_rule');
        $form  = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Behavior Action')]);
        $fieldset->addField('action', 'select', [
            'label'  => __('Actions'),
            'title'  => __('Actions'),
            'name'   => 'action',
            'values' => PointActions::getOptionArray()
        ]);
        $fieldset->addField('point_amount', 'text', [
            'label'    => __('Fixed amount'),
            'title'    => __('Fixed amount'),
            'class'    => 'validate-digits validate-greater-than-zero',
            'required' => true,
            'name'     => 'point_amount',
        ]);
        $fieldset->addField('max_point', 'text', [
            'label' => __('Maximum Earning Points'),
            'title' => __('Maximum Earning Points'),
            'name'  => 'max_point',
            'note'  => __('Set the maximum number of earning points. If empty or zero, there is no limitation')
        ]);
        $fieldset->addField('max_point_period', 'select', [
            'label'  => __('Max point earn period'),
            'title'  => __('Max point earn period'),
            'name'   => 'max_point_period',
            'values' => PointPeriod::getOptionArray(),
        ]);

        $fieldset->addField('frequency', 'select', [
            'name'   => 'frequency',
            'label'  => __('Frequency'),
            'title'  => __('Frequency'),
            'values' => $this->frequency->toOptionArray()
        ]);

        $fieldset->addField('start_day', 'select', [
            'name'   => 'start_day',
            'label'  => __('Start Day'),
            'title'  => __('Start Day'),
            'values' => $this->daysOfWeek->toOptionArray(),
            'note'   => __('Day of week')
        ]);

        $fieldset->addField('start_date', 'select', [
            'name'   => 'start_date',
            'label'  => __('Start Date'),
            'title'  => __('Start Date'),
            'values' => $this->daysOfMonth->toOptionArray(),
            'note'   => __('Date of month')
        ]);

        $fieldset->addField('start_month', 'select', [
            'name'   => 'start_month',
            'label'  => __('Start Month'),
            'title'  => __('Start Month'),
            'values' => $this->monthsOfYear->toOptionArray()
        ]);

        $fieldset->addField('expire_after', 'text', [
            'name'  => 'expire_after',
            'label' => __('Expire After'),
            'title' => __('Expire After'),
            'class' => 'validate-number validate-zero-or-greater',
            'note'  => __('Day(s). Will expire after the selected time period from the date of receiving points. If empty, there is no expiration time for the point.')
        ]);

        if ($model->getRuleId()) {
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
        return __('Actions');
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
