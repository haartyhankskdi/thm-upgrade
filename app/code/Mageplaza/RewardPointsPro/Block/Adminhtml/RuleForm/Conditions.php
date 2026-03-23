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
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions as RuleConditions;
use Magento\Rule\Model\Condition\AbstractCondition;

/**
 * Class Conditions
 * @package Mageplaza\RewardPointsPro\Block\Adminhtml\RuleForm
 */
class Conditions extends Generic implements TabInterface
{
    /**
     * @var string
     */
    protected $_modelRegistry = 'catalog_earning_rule';

    /**
     * @var Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var RuleConditions
     */
    protected $_conditions;

    /**
     * Conditions constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param RuleConditions $conditions
     * @param Fieldset $rendererFieldset
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        RuleConditions $conditions,
        Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->_conditions = $conditions;
        $this->_rendererFieldset = $rendererFieldset;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry($this->_modelRegistry);
        /** @var Form $form */
        $form = $this->addTabToForm($model);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param $model
     * @param string $fieldsetId
     * @param string $formName
     *
     * @return Form
     * @throws LocalizedException
     */
    protected function addTabToForm($model, $fieldsetId = 'conditions_fieldset', $formName = 'sales_rule_form')
    {
        /** @var Form $form */
        $form = $this->_formFactory->create();

        $conditionsFieldSetId = $model->getConditionsFieldSetId($formName);
        $renderer = $this->_rendererFieldset->setNameInLayout('mp_rewardpro_earning_shopping_cart_tabs_conditions')->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl(
                $this->getUrl(
                    'sales_rule/promo_quote/newConditionHtml/form/' . $conditionsFieldSetId,
                    ['form_namespace' => $formName]
                )
            )
            ->setFieldSetId($conditionsFieldSetId);

        $fieldset = $form->addFieldset(
            $fieldsetId,
            ['legend' => __('Apply the rule only if the following conditions are met (leave blank for all products).')]
        )
            ->setRenderer($renderer);

        $fieldset->addField(
            'conditions',
            'text',
            [
                'name' => 'conditions',
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'required' => true,
                'data-form-part' => $formName
            ]
        )->setRule($model)->setRenderer($this->_conditions);

        $this->addExtraFieldset($form, $fieldset);

        $form->setValues($model->getData());
        $model->getConditions()->setJsFormObject($model->getConditionsFieldSetId($formName));
        $this->setConditionFormName($model->getConditions(), $formName);

        return $form;
    }

    /**
     * @param $form
     * @param $fieldset
     *
     * @return $this
     */
    public function addExtraFieldset($form, $fieldset)
    {
        return $this;
    }

    /**
     * Handles addition of form name to condition and its conditions.
     *
     * @param AbstractCondition $conditions
     * @param $formName
     *
     * @throws LocalizedException
     */
    private function setConditionFormName(AbstractCondition $conditions, $formName)
    {
        $conditions->setFormName($formName);
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName);
            }
        }
    }

    /**
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Conditions');
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
