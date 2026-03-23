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
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Block\Adminhtml\Earning\Catalog;

use Magento\Backend\Block\Widget\Form\Container;

/**
 * Class Edit
 * @package Mageplaza\RewardPointsPro\Block\Adminhtml\Earning\Catalog\Edit
 */
class Edit extends Container
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_objectId = 'rule_id';
        $this->_blockGroup = 'Mageplaza_RewardPointsPro';
        $this->_controller = 'adminhtml_earning_catalog';

        $this->buttonList->add(
            'save_and_apply',
            [
                'label' => __('Save and Apply'),
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'save',
                            'target' => '#edit_form',
                            'eventData' => ['action' => ['args' => ['is_apply' => '1']]],
                        ],
                    ],
                ],
                'class' => 'save'
            ],
            20
        );

        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'class' => 'save',
                'label' => __('Save and Continue Edit'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            30
        );

        $this->_formScripts[] ='
            require([
                "jquery",
            ], function ($) {
              $(".save").click(function() {
                 $("body").trigger("processStart");
                    if (!$("#edit_form").valid()) {
                        $("body").trigger("processStop");
                    }
              })
            });
        ';

        parent::_construct();
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('mpreward/earning_catalog/index');
    }
}
