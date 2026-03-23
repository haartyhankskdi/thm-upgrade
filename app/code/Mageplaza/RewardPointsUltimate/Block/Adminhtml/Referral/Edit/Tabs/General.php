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

use Mageplaza\RewardPointsPro\Block\Adminhtml\RuleForm\General as GeneralForm;

/**
 * Class General
 * @package Mageplaza\RewardPointsUltimate\Block\Adminhtml\Referral\Edit\Tabs
 */
class General extends GeneralForm
{
    /**
     * @var string
     */
    protected $_modelRegistry = 'refer_rule';

    /**
     * @param $form
     * @param null $fieldset
     *
     * @return $this
     */
    public function addExtraFieldset($form, $fieldset = null)
    {
        $fieldset->addField(
            'referral_group_ids',
            'multiselect',
            [
                'name'     => 'referral_group_ids',
                'title'    => __('Referral Group(s)'),
                'required' => true,
                'label'    => __('Referral Group(s)'),
                'values'   => $this->getCustomerGroups()
            ],
            'customer_group_ids'
        );

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisplayCustomerGroupNotLogin($includeNotLogin = false)
    {
        return $includeNotLogin;
    }
}
