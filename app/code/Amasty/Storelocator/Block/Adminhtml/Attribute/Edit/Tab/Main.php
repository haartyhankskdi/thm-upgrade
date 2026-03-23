<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Block\Adminhtml\Attribute\Edit\Tab;

use Amasty\Storelocator\Model\Config\Source\AttributeStatuses;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class Main extends Generic implements TabInterface
{
    public const ALLOWED_ATTRIBUTES = ["select", "multiselect", "boolean", "text"];

    public const FILTER_ATTRIBUTES = ["select", "multiselect", "boolean"];

    /**
     * @var AttributeStatuses
     */
    private $attributeStatuses;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = [],
        ?AttributeStatuses $attributeStatuses = null
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        // OM for backward compatibility
        $this->attributeStatuses = $attributeStatuses ?? ObjectManager::getInstance()->get(AttributeStatuses::class);
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Properties');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $attributeObject = $this->getAttributeObject();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Attribute Properties')]);

        $attributeId = $attributeObject->getId();
        if ($attributeId) {
            $fieldset->addField('attribute_id', 'hidden', ['name' => 'attribute_id']);
        }

        $validateClass = sprintf(
            'validate-code validate-length maximum-length-%d',
            \Magento\Eav\Model\Entity\Attribute::ATTRIBUTE_CODE_MAX_LENGTH
        );
        $fieldset->addField(
            'attribute_code',
            'text',
            [
                'name' => 'attribute_code',
                'label' => __('Attribute Code'),
                'title' => __('Attribute Code'),
                'note' => __(
                    'The code is used internally. Make sure you don\'t use spaces or more than %1 symbols.',
                    \Magento\Eav\Model\Entity\Attribute::ATTRIBUTE_CODE_MAX_LENGTH
                ),
                'class' => $validateClass,
                'required' => $attributeId ? false : true,
                'disabled' => $attributeId ? true : false,
            ]
        );

        $fieldset->addField(
            'frontend_label',
            'text',
            [
                'name' => 'frontend_label[0]',
                'label' => __('Default Label'),
                'title' => __('Default label'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'frontend_input',
            'select',
            [
                'name' => 'frontend_input',
                'label' => __('Catalog Input Type for Store Owner'),
                'title' => __('Catalog Input Type for Store Owner'),
                'value' => 'text',
                'values' => $this->getFrontendTypes()
            ]
        );

        $fieldset = $form->addFieldset(
            'option_fieldset',
            ['legend' => __('Manage Options (Values of Your Attribute)')]
        );

        $fieldset->addField(
            'options',
            'text',
            [
                'name' => 'options',
                'label' => __('Options'),
                'title' => __('Options')
            ]
        );

        $form->getElement(
            'options'
        )->setRenderer(
            $this->getLayout()
                ->createBlock(\Amasty\Storelocator\Block\Adminhtml\Attribute\Edit\Tab\Option\Field::class)
        );

        $form->setValues($attributeObject->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getAttributeObject()
    {
        return $this->_coreRegistry->registry('current_amasty_storelocator_attribute');
    }

    /**
     * @return array
     */
    private function getFrontendTypes(): array
    {
        return $this->attributeStatuses->toOptionArray();
    }
}
