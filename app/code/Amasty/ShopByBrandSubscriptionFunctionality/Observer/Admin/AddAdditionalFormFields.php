<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand Subscription Functionality
 */

namespace Amasty\ShopByBrandSubscriptionFunctionality\Observer\Admin;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBrand\Model\Brand\BrandDataInterface;
use Amasty\ShopbyBrand\Model\ConfigProvider;
use Amasty\ShopByBrandSubscriptionFunctionality\Model\Source\BrandInfoPosition as BrandInfoPositionSource;
use Amasty\ShopByBrandSubscriptionFunctionality\Model\Source\PageLayout as PageLayoutSource;
use Magento\Backend\Block\Widget\Form\Element\Dependence as DependenceBlock;
use Magento\Config\Model\Config\Source\Yesno as YesnoSource;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;

class AddAdditionalFormFields implements ObserverInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var PageLayoutSource
     */
    private $pageLayoutSource;

    /**
     * @var YesnoSource
     */
    private $yesnoSource;

    /**
     * @var BrandInfoPositionSource
     */
    private $brandInfoPositionSource;

    public function __construct(
        ConfigProvider $configProvider,
        LayoutInterface $layout,
        PageLayoutSource $pageLayoutSource,
        YesnoSource $yesnoSource,
        BrandInfoPositionSource $brandInfoPositionSource
    ) {
        $this->configProvider = $configProvider;
        $this->layout = $layout;
        $this->pageLayoutSource = $pageLayoutSource;
        $this->yesnoSource = $yesnoSource;
        $this->brandInfoPositionSource = $brandInfoPositionSource;
    }

    /**
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Form $form */
        $form = $observer->getData('form');
        if (!$form) {
            return;
        }

        /** @var OptionSettingInterface $setting */
        $setting = $observer->getData('setting');
        if (!$setting) {
            return;
        }

        $storeId = (int)$observer->getData('store_id');
        if ($this->configProvider->getBrandAttributeCode($storeId) === $setting->getAttributeCode()) {
            $this->addDesignFieldset($form);
            $this->addBrandInfoFields($form);
        }
    }

    private function addDesignFieldset(Form $form)
    {
        $designFieldset = $form->addFieldset(
            'design_fieldset',
            [
                'legend' => __('Design'),
                'class' => 'form-inline'
            ],
            'product_list_fieldset'
        );

        $designFieldset->addField(
            'page_layout',
            'select',
            [
                'name' => BrandDataInterface::PAGE_LAYOUT,
                'label' => __('Layout'),
                'title' => __('Layout'),
                'values' => $this->pageLayoutSource->toOptionArray()
            ]
        );
    }

    private function addBrandInfoFields(Form $form): void
    {
        if (!$pageContentFieldset = $form->getElements()->searchById('product_list_fieldset')) {
            return;
        }

        $showBrandInfoElement = $this->getShowBrandInfoElement($pageContentFieldset);

        $brandInfoPositionElement = $pageContentFieldset->addField(
            OptionSettingInterface::BRAND_INFO_BLOCK_POSITION,
            'multiselect',
            [
                'name' => OptionSettingInterface::BRAND_INFO_BLOCK_POSITION,
                'label' => __('Brand Info Block Position'),
                'title' => __('Brand Info Block Position'),
                'values' => $this->brandInfoPositionSource->toOptionArray()
            ],
            OptionSettingInterface::SHOW_BRAND_INFO
        );

        $brandInfoPostalAddressElement = $pageContentFieldset->addField(
            OptionSettingInterface::BRAND_INFO_POSTAL_ADDRESS,
            'text',
            [
                'name' => OptionSettingInterface::BRAND_INFO_POSTAL_ADDRESS,
                'label' => __('Manufacturer or Importer Postal Address'),
                'title' => __('Manufacturer or Importer Postal Address')
            ],
            OptionSettingInterface::BRAND_INFO_BLOCK_POSITION
        );

        $brandInfoElectronicAddressElement = $pageContentFieldset->addField(
            OptionSettingInterface::BRAND_INFO_ELECTRONIC_ADDRESS,
            'text',
            [
                'name' => OptionSettingInterface::BRAND_INFO_ELECTRONIC_ADDRESS,
                'label' => __('Manufacturer or Importer Electronic Address'),
                'title' => __('Manufacturer or Importer Electronic Address')
            ],
            OptionSettingInterface::BRAND_INFO_POSTAL_ADDRESS
        );

        $brandInfoContactElement = $pageContentFieldset->addField(
            OptionSettingInterface::BRAND_INFO_CONTACT,
            'text',
            [
                'name' => OptionSettingInterface::BRAND_INFO_CONTACT,
                'label' => __('Responsible Person’s Contact Information'),
                'title' => __('Responsible Person’s Contact Information')
            ],
            OptionSettingInterface::BRAND_INFO_ELECTRONIC_ADDRESS
        );

        /** @var DependenceBlock $dependenceBlock */
        $dependenceBlock = $this->layout->createBlock(DependenceBlock::class);

        $dependenceBlock->addFieldMap($showBrandInfoElement->getHtmlId(), $showBrandInfoElement->getName());
        $dependenceBlock->addFieldMap($brandInfoPositionElement->getHtmlId(), $brandInfoPositionElement->getName());
        $dependenceBlock->addFieldMap(
            $brandInfoPostalAddressElement->getHtmlId(),
            $brandInfoPostalAddressElement->getName()
        );
        $dependenceBlock->addFieldMap(
            $brandInfoElectronicAddressElement->getHtmlId(),
            $brandInfoElectronicAddressElement->getName()
        );
        $dependenceBlock->addFieldMap($brandInfoContactElement->getHtmlId(), $brandInfoContactElement->getName());

        $dependenceBlock->addFieldDependence($brandInfoPositionElement->getName(), $showBrandInfoElement->getName(), 1);
        $dependenceBlock->addFieldDependence(
            $brandInfoPostalAddressElement->getName(),
            $showBrandInfoElement->getName(),
            1
        );
        $dependenceBlock->addFieldDependence(
            $brandInfoElectronicAddressElement->getName(),
            $showBrandInfoElement->getName(),
            1
        );
        $dependenceBlock->addFieldDependence($brandInfoContactElement->getName(), $showBrandInfoElement->getName(), 1);

        $pageContentFieldset->addField(
            'brand_info_deps',
            'hidden',
            [
                'after_element_html' => $dependenceBlock->toHtml()
            ],
            OptionSettingInterface::BRAND_INFO_CONTACT
        );
    }

    private function getShowBrandInfoElement(AbstractElement $pageContentFieldset): AbstractElement
    {
        $showBrandInfoElement = $pageContentFieldset->getElements()->searchById('show_brand_info');
        if (!$showBrandInfoElement) {
            $pageContentFieldset->addField(
                OptionSettingInterface::SHOW_BRAND_INFO,
                'select',
                [
                    'name' => OptionSettingInterface::SHOW_BRAND_INFO,
                    'label' => __('Display Additional Brand Information'),
                    'title' => __('Display Additional Brand Information'),
                    'values' => $this->yesnoSource->toOptionArray()
                ],
                OptionSettingInterface::BOTTOM_CMS_BLOCK_ID
            );
        }

        $showBrandInfoElement->setData('disabled', false);
        $showBrandInfoElement->setData(
            'note',
            sprintf(
                'Enabling this setting will display the brand additional information required by the EU on '
                . ' the brand page, in compliance with <a href="%s" target="_blank">Regulation (EU) 2023/988</a>'
                . ' on general product safety.',
                'https://eur-lex.europa.eu/legal-content/EN/TXT/?uri=CELEX%3A32023R0988'
            )
        );

        return $showBrandInfoElement;
    }
}
