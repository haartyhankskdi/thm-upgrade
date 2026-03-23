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

namespace Mageplaza\RewardPointsPro\Block\Adminhtml\Earning\Catalog\Edit\Tabs;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Mageplaza\RewardPointsPro\Model\Source\Catalogrule\Earning as CatalogEarningOptions;

/**
 * Class Actions
 * @package Mageplaza\RewardPointsPro\Block\Adminhtml\Earning\Catalog\Edit\Tabs
 */
class Actions extends Generic implements TabInterface
{
    /**
     * @var CatalogEarningOptions
     */
    protected $catalogEarningOptions;

    /**
     * @var Yesno
     */
    protected $sourceYesno;

    /**
     * @var FieldFactory
     */
    protected $fieldFactory;

    /**
     * Actions constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param CatalogEarningOptions $catalogEarningOptions
     * @param Yesno $sourceYesno
     * @param FieldFactory $fieldFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        CatalogEarningOptions $catalogEarningOptions,
        Yesno $sourceYesno,
        FieldFactory $fieldFactory,
        array $data = []
    ) {
        $this->sourceYesno           = $sourceYesno;
        $this->catalogEarningOptions = $catalogEarningOptions;
        $this->fieldFactory          = $fieldFactory;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('catalog_earning_rule');
        $form  = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Earning Point Action')]);
        $fieldset->addField('is_apply', 'hidden', ['name' => 'is_apply']);
        $action = $fieldset->addField('action', 'select', [
            'label'  => __('Action'),
            'title'  => __('Action'),
            'name'   => 'action',
            'values' => $this->catalogEarningOptions->toOptionArray(),
            'note'   => __('select the type to earn points'),
        ]);
        $fieldset->addField('point_amount', 'text', [
            'label'    => __('Points (X)'),
            'title'    => __('Points (X)'),
            'class'    => 'validate-digits validate-greater-than-zero',
            'required' => true,
            'name'     => 'point_amount',
        ]);
        $moneyStep = $fieldset->addField('money_step', 'text', [
            'label'    => __('Money Step (Y)'),
            'title'    => __('Money Step (Y)'),
            'class'    => 'validate-digits validate-greater-than-zero',
            'required' => true,
            'name'     => 'money_step',
        ]);
        $maxPoints = $fieldset->addField('max_points', 'text', [
            'label' => __('Maximum Earning Points'),
            'title' => __('Maximum Earning Points'),
            'name'  => 'max_points',
            'note'  => __('Set the maximum number spending points.If empty or zero, there is no limitation.')
        ]);
        $fieldset->addField('stop_rules_processing', 'select', [
            'label'  => __('Stop further rules processing'),
            'title'  => __('Stop further rules processing'),
            'name'   => 'stop_rules_processing',
            'values' => $this->sourceYesno->toOptionArray()
        ]);

        $blockDependence = $this->getLayout()->createBlock(Dependence::class);
        $dependAction    = $this->fieldFactory->create(
            [
                'fieldData'   => [
                    'value'     => implode(
                        ',',
                        [
                            CatalogEarningOptions::TYPE_PRICE,
                            CatalogEarningOptions::TYPE_PROFIT
                        ]
                    ),
                    'separator' => ','
                ],
                'fieldPrefix' => ''
            ]
        );

        $dependMaxPoints = $this->fieldFactory->create(
            [
                'fieldData'   => [
                    'value'     => implode(
                        ',',
                        [
                            CatalogEarningOptions::TYPE_PRICE,
                            CatalogEarningOptions::TYPE_PROFIT,
                            CatalogEarningOptions::TYPE_PERCENT
                        ]
                    ),
                    'separator' => ','
                ],
                'fieldPrefix' => ''
            ]
        );

        $blockDependence->addFieldMap($action->getHtmlId(), $action->getName())
            ->addFieldMap($moneyStep->getHtmlId(), $moneyStep->getName())
            ->addFieldDependence($moneyStep->getName(), $action->getName(), $dependAction)
            ->addFieldMap($maxPoints->getHtmlId(), $maxPoints->getName())
            ->addFieldDependence($maxPoints->getName(), $action->getName(), $dependMaxPoints);

        $this->setChild('form_after', $blockDependence);
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
