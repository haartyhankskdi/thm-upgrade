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

namespace Mageplaza\RewardPointsUltimate\Block\Adminhtml\Referral\Edit\Tabs;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Rule\Block\Actions as RuleAction;
use Mageplaza\RewardPointsPro\Block\Adminhtml\RuleForm\Actions as ActionsForm;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerActions;
use Mageplaza\RewardPointsUltimate\Model\Source\DiscountType;
use Mageplaza\RewardPointsUltimate\Model\Source\ReferralActions;

/**
 * Class Actions
 * @package Mageplaza\RewardPointsUltimate\Block\Adminhtml\Referral\Edit\Tabs
 */
class Actions extends ActionsForm
{
    /**
     * @var string
     */
    protected $_modelRegistry = 'refer_rule';

    /**
     * @var CustomerActions
     */
    protected $customerActions;

    /**
     * @var ReferralActions
     */
    protected $referralActions;

    /**
     * @var DiscountType
     */
    protected $discountType;

    /**
     * @var FieldFactory
     */
    protected $fieldFactory;

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
     * @param RuleAction $ruleActions
     * @param Yesno $sourceYesno
     * @param Fieldset $rendererFieldset
     * @param CustomerActions $customerActions
     * @param ReferralActions $referralActions
     * @param FieldFactory $fieldFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        RuleAction $ruleActions,
        Yesno $sourceYesno,
        Fieldset $rendererFieldset,
        CustomerActions $customerActions,
        ReferralActions $referralActions,
        FieldFactory $fieldFactory,
        array $data = []
    ) {
        $this->customerActions = $customerActions;
        $this->referralActions = $referralActions;
        $this->fieldFactory = $fieldFactory;
        $this->sourceYesno = $sourceYesno;

        parent::__construct($context, $registry, $formFactory, $ruleActions, $rendererFieldset, $data);
    }

    /**
     * @param $form
     *
     * @return $this
     * @throws LocalizedException
     */
    public function addExtraFieldset($form)
    {
        $blockDependence = $this->getLayout()->createBlock(Dependence::class);
        $this->addCustomerActions($form, $blockDependence);
        $this->addReferralActions($form, $blockDependence);
        $this->setChild('form_after', $blockDependence);

        return $this;
    }

    /**
     * @param $form
     * @param $blockDependence
     */
    public function addCustomerActions($form, $blockDependence)
    {
        $customerFieldset = $form->addFieldset('customer_fieldset', ['legend' => __('Customer Action')]);
        $customerAction = $customerFieldset->addField('customer_action', 'select', [
            'label' => __('Action'),
            'title' => __('Action'),
            'name' => 'customer_action',
            'values' => $this->customerActions->toOptionArray()
        ]);
        $customerPoints = $customerFieldset->addField('customer_points', 'text', [
            'label' => __('Points (X)'),
            'title' => __('Points (X)'),
            'class' => 'validate-digits validate-greater-than-zero',
            'name' => 'customer_points',
            'required' => true
        ]);
        $customerMoneyStep = $customerFieldset->addField('customer_money_step', 'text', [
            'label' => __('Money Step (Y)'),
            'title' => __('Money Step (Y)'),
            'class' => 'validate-digits validate-greater-than-zero',
            'name' => 'customer_money_step',
            'required' => true
        ]);
        $customerDiscount = $customerFieldset->addField('customer_discount', 'text', [
            'label' => __('Discount'),
            'title' => __('Discount'),
            'name' => 'customer_discount',
            'required' => true
        ]);
        $customerFieldset->addField('customer_apply_to_shipping', 'select', [
            'label' => __('Apply to shipping'),
            'title' => __('Apply to shipping'),
            'name' => 'customer_apply_to_shipping',
            'values' => $this->sourceYesno->toOptionArray()
        ]);
        $customerFieldset->addField('stop_rules_processing', 'select', [
            'label' => __('Stop further rules processing'),
            'title' => __('Stop further rules processing'),
            'name' => 'stop_rules_processing',
            'values' => $this->sourceYesno->toOptionArray()
        ]);

        $blockDependence->addFieldMap($customerAction->getHtmlId(), $customerAction->getName())
            ->addFieldMap($customerPoints->getHtmlId(), $customerPoints->getName())
            ->addFieldDependence(
                $customerPoints->getName(),
                $customerAction->getName(),
                $this->fieldFactory->create(
                    [
                        'fieldData' => [
                            'value' => implode(',', [
                                CustomerActions::TYPE_FIXED_POINTS,
                                CustomerActions::TYPE_PRICE
                            ]),
                            'separator' => ','
                        ],
                        'fieldPrefix' => ''
                    ]
                )
            )
            ->addFieldMap($customerMoneyStep->getHtmlId(), $customerMoneyStep->getName())
            ->addFieldDependence($customerMoneyStep->getName(), $customerAction->getName(), CustomerActions::TYPE_PRICE)
            ->addFieldMap($customerDiscount->getHtmlId(), $customerDiscount->getName())
            ->addFieldDependence(
                $customerDiscount->getName(),
                $customerAction->getName(),
                $this->fieldFactory->create(
                    [
                        'fieldData' => [
                            'value' => implode(',', [
                                CustomerActions::TYPE_PERCENT,
                                CustomerActions::TYPE_FIXED_DISCOUNT
                            ]),
                            'separator' => ','
                        ],
                        'fieldPrefix' => ''
                    ]
                )
            );
    }

    /**
     * @param $form
     * @param $blockDependence
     */
    public function addReferralActions($form, $blockDependence)
    {
        $referralsFieldset = $form->addFieldset('referrals_fieldset', ['legend' => __('Referral\'s Action')]);
        $referralsActions = $referralsFieldset->addField('referral_type', 'select', [
            'label' => __('Referral\'s action type'),
            'title' => __('Referral\'s action type'),
            'name' => 'referral_type',
            'values' => $this->referralActions->toOptionArray()
        ]);
        $referralPoints = $referralsFieldset->addField('referral_points', 'text', [
            'label' => __('Referral\'s points (X)'),
            'title' => __('Referral\'s points (X)'),
            'class' => 'validate-digits validate-greater-than-zero',
            'name' => 'referral_points',
            'required' => true
        ]);
        $referralMoneyStep = $referralsFieldset->addField('referral_money_step', 'text', [
            'label' => __('Referral\'s Money Step (Y)'),
            'title' => __('Referral\'s Money Step (Y)'),
            'class' => 'validate-digits validate-greater-than-zero',
            'name' => 'referral_money_step',
            'required' => true
        ]);

        $blockDependence->addFieldMap($referralsActions->getHtmlId(), $referralsActions->getName())
            ->addFieldMap($referralPoints->getHtmlId(), $referralPoints->getName())
            ->addFieldDependence(
                $referralPoints->getName(),
                $referralsActions->getName(),
                $this->fieldFactory->create(
                    [
                        'fieldData' => [
                            'value' => implode(',', [
                                ReferralActions::TYPE_PRICE,
                                ReferralActions::TYPE_FIXED
                            ]),
                            'separator' => ','
                        ],
                        'fieldPrefix' => ''
                    ]
                )
            )->addFieldMap($referralMoneyStep->getHtmlId(), $referralMoneyStep->getName())
            ->addFieldDependence(
                $referralMoneyStep->getName(),
                $referralsActions->getName(),
                ReferralActions::TYPE_PRICE
            );
    }
}
