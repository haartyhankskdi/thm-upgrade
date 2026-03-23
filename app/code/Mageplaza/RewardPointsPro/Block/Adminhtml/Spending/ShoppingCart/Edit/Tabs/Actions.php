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

namespace Mageplaza\RewardPointsPro\Block\Adminhtml\Spending\ShoppingCart\Edit\Tabs;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Rule\Block\Actions as RuleActions;
use Mageplaza\RewardPointsPro\Block\Adminhtml\RuleForm\Actions as ActionsForm;
use Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\DiscountStyle;

/**
 * Class Actions
 * @package Mageplaza\RewardPointsPro\Block\Adminhtml\Spending\ShoppingCart\Edit\Tabs
 */
class Actions extends ActionsForm
{
    /**
     * @var \Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\Actions
     */
    protected $discountStyleOptions;

    /**
     * @var Yesno
     */
    protected $sourceYesno;

    /**
     * Actions constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param RuleActions $ruleActions
     * @param DiscountStyle $discountStyleOptions
     * @param Yesno $yesno
     * @param Fieldset $rendererFieldset
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        RuleActions $ruleActions,
        DiscountStyle $discountStyleOptions,
        Yesno $yesno,
        Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->discountStyleOptions = $discountStyleOptions;
        $this->sourceYesno = $yesno;

        parent::__construct($context, $registry, $formFactory, $ruleActions, $rendererFieldset, $data);
    }

    /**
     * @param $form
     *
     * @return $this
     */
    public function addExtraFieldset($form)
    {
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Update prices using the following information')]
        );
        $fieldset->addField('rule_type', 'hidden', ['name' => 'rule_type']);
        $fieldset->addField('discount_style', 'select', [
            'label' => __('Action'),
            'title' => __('Action'),
            'name' => 'discount_style',
            'values' => $this->discountStyleOptions->toOptionArray()
        ]);
        $fieldset->addField('discount_amount', 'text', [
            'label' => __('Discount Amount'),
            'title' => __('Discount Amount'),
            'class' => 'validate-digits validate-greater-than-zero',
            'name' => 'discount_amount',
            'required' => true,
            'note' => __('Discount received for every X points in tab Conditions')

        ]);
        $fieldset->addField('apply_to_shipping', 'select', [
            'label' => __('Apply to shipping'),
            'title' => __('Apply to shipping'),
            'name' => 'apply_to_shipping',
            'values' => $this->sourceYesno->toOptionArray()
        ]);

        return $this;
    }
}
