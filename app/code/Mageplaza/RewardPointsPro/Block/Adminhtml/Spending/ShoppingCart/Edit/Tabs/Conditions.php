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
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions as RuleConditions;
use Mageplaza\RewardPointsPro\Block\Adminhtml\RuleForm\Conditions as ConditionsForm;
use Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\OptionsSpending;

/**
 * Class Conditions
 * @package Mageplaza\RewardPointsPro\Block\Adminhtml\Spending\ShoppingCart\Edit\Tabs
 */
class Conditions extends ConditionsForm
{
    /**
     * @var string
     */
    protected $_modelRegistry = 'shopping_cart_spending_rule';

    /**
     * @var OptionsSpending
     */
    protected $optionSpending;

    /**
     * Conditions constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param RuleConditions $conditions
     * @param Fieldset $rendererFieldset
     * @param OptionsSpending $optionsSpending
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        RuleConditions $conditions,
        Fieldset $rendererFieldset,
        OptionsSpending $optionsSpending,
        array $data = []
    ) {
        $this->optionSpending = $optionsSpending;

        parent::__construct($context, $registry, $formFactory, $conditions, $rendererFieldset, $data);
    }

    /**
     * @param $form
     * @param $fieldset
     *
     * @return $this
     * @throws LocalizedException
     */
    public function addExtraFieldset($form, $fieldset)
    {
        $form->setFieldNameSuffix('rule');
        $fieldset = $form->addFieldset('action_fieldset', ['legend' => __('Spending Point Action')]);

        $action = $fieldset->addField('action', 'select', [
            'label' => __('Action'),
            'title' => __('Action'),
            'name' => 'action',
            'values' => $this->optionSpending->toOptionArray(),
            'note' => __('select the type to spend points')
        ]);
        $fieldset->addField('point_amount', 'text', [
            'label' => __('Points (X)'),
            'title' => __('Points (X)'),
            'class' => 'validate-digits validate-greater-than-zero',
            'required' => true,
            'name' => 'point_amount',
        ]);
        $maxPoint = $fieldset->addField('max_points', 'text', [
            'label' => __('Maximum Redeemable Points'),
            'title' => __('Maximum Redeemable Points'),
            'name' => 'max_points',
        ]);

        $blockDependence = $this->getLayout()->createBlock(Dependence::class);
        $blockDependence->addFieldMap($action->getHtmlId(), $action->getName())
            ->addFieldMap($maxPoint->getHtmlId(), $maxPoint->getName())
            ->addFieldDependence($maxPoint->getName(), $action->getName(), OptionsSpending::TYPE_PRICE);

        $this->setChild('form_after', $blockDependence);

        return $this;
    }
}
