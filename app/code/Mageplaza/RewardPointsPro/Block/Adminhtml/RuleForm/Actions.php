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
use Magento\Rule\Block\Actions as RuleActions;
use Magento\Rule\Model\Condition\AbstractCondition;
use Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\Type;

/**
 * Class Actions
 * @package Mageplaza\RewardPointsPro\Block\Adminhtml\RuleForm
 */
class Actions extends Generic implements TabInterface
{
    /**
     * @var string
     */
    protected $_modelRegistry = 'shopping_cart_spending_rule';

    /**
     * @var int
     */
    protected $ruleType = Type::SHOPPING_CART_SPENDING;

    /**
     * @var Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var RuleActions
     */
    protected $_ruleActions;

    /**
     * Actions constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param RuleActions $ruleActions
     * @param Fieldset $rendererFieldset
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        RuleActions $ruleActions,
        Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_ruleActions      = $ruleActions;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry($this->_modelRegistry);
        /** @var Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');
        $this->addExtraFieldset($form);
        $formName          = 'sales_rule_form';
        $actionsFieldSetId = $model->getActionsFieldSetId($formName);
        $newChildUrl       = $this->getUrl(
            'sales_rule/promo_quote/newActionHtml/form/' . $actionsFieldSetId,
            ['form_namespace' => $formName]
        );

        if (in_array($this->getRequest()->getFullActionName(), ['mpreward_spending_shoppingcart_edit', 'mprewardultimate_referral_edit', 'mpreward_earning_shoppingcart_edit'])) {
            $renderer = $this->getLayout()->createBlock(Fieldset::class)
                ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
                ->setNewChildUrl($newChildUrl)
                ->setFieldSetId($actionsFieldSetId)
                ->setNameInLayout('mp_reward_point_rule_conditions_fieldset');
        } else {
            $renderer = $this->getLayout()->createBlock(Fieldset::class)
                ->setNameInLayout('mp_rewardpro_earning_shopping_cart_tabs_actions')
                ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
                ->setNewChildUrl($newChildUrl)
                ->setFieldSetId($actionsFieldSetId)
                ->setNameInLayout('mp_rewardpoints_pro_rule_conditions_fieldset');
        }
        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            ['legend' => __('Apply the rule only to cart items matching the following conditions (leave blank for all items).')]
        )
            ->setRenderer($renderer);
        $fieldset->addField('actions', 'text', [
            'name'           => 'apply_to',
            'label'          => __('Apply To'),
            'title'          => __('Apply To'),
            'required'       => true,
            'data-form-part' => $formName
        ])->setRule($model)->setRenderer($this->_ruleActions);

        $form->setValues(['rule_type' => $this->ruleType]);
        $this->setActionFormName($model->getActions(), $formName);

        if ($model->getRuleId()) {
            $form->setValues($model->getData());
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param $form
     *
     * @return $this
     */
    public function addExtraFieldset($form)
    {
        return $this;
    }

    /**
     * Handles addition of form name to action and its actions.
     *
     * @param AbstractCondition $actions
     * @param string $formName
     *
     * @return void
     */
    private function setActionFormName(AbstractCondition $actions, $formName)
    {
        $actions->setFormName($formName);
        if ($actions->getActions() && is_array($actions->getActions())) {
            foreach ($actions->getActions() as $condition) {
                $this->setActionFormName($condition, $formName);
            }
        }
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
