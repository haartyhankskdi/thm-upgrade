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

use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Class Labels
 * @package Mageplaza\RewardPointsPro\Block\Adminhtml\Spending\ShoppingCart\Edit\Tabs
 */
class Labels extends Generic implements TabInterface
{
    /**
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('shopping_cart_spending_rule');
        /** @var Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $labels = $model->getStoreLabels();
        $fieldset = $form->addFieldset('default_label_fieldset', ['legend' => __('Default Label')]);
        $fieldset->addField('store_default_label', 'text', [
            'name' => 'store_labels[0]',
            'required' => true,
            'label' => __('Default Rule Label for All Store Views'),
            'value' => isset($labels[0]) ? $labels[0] : '',
        ]);

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->_createStoreSpecificFieldset($form, $labels);
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Create store specific fieldset
     *
     * @param $form
     * @param $labels
     *
     * @return mixed
     * @throws LocalizedException
     */
    protected function _createStoreSpecificFieldset($form, $labels)
    {
        $fieldset = $form->addFieldset(
            'store_labels_fieldset',
            ['legend' => __('Store View Specific Labels'), 'class' => 'store-scope']
        );
        $renderer = $this->getLayout()->createBlock(Fieldset::class);
        $fieldset->setRenderer($renderer);

        foreach ($this->_storeManager->getWebsites() as $website) {
            $fieldset->addField(
                "w_{$website->getId()}_label",
                'note',
                [
                    'label' => $website->getName(),
                    'fieldset_html_class' => 'website'
                ]
            );
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField(
                    "sg_{$group->getId()}_label",
                    'note',
                    [
                        'label' => $group->getName(),
                        'fieldset_html_class' => 'store-group'
                    ]
                );
                foreach ($stores as $store) {
                    $fieldset->addField(
                        "s_{$store->getId()}",
                        'text',
                        [
                            'name' => 'store_labels[' . $store->getId() . ']',
                            'title' => $store->getName(),
                            'label' => $store->getName(),
                            'required' => false,
                            'value' => isset($labels[$store->getId()]) ? $labels[$store->getId()] : '',
                            'fieldset_html_class' => 'store',
                            'data-form-part' => 'sales_rule_form'
                        ]
                    );
                }
            }
        }

        return $fieldset;
    }

    /**
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Labels');
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
