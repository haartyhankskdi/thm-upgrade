<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Amasty Shop By Brand GraphQl for Magento 2 (System)
 */

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Api\Data\OptionSettingRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionValueProvider;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;

$objectManager = Bootstrap::getObjectManager();

/** @var Config $eavConfig */
$eavConfig = $objectManager->get(Config::class);

/** @var Collection $options */
$options = $objectManager->create(Collection::class);

/** @var OptionValueProvider $optionValueProvider */
$optionValueProvider = $objectManager->get(OptionValueProvider::class);

/** @var OptionSettingRepositoryInterface $optionSettingRepository */
$optionSettingRepository = $objectManager->create(OptionSettingRepositoryInterface::class);

$attribute = $eavConfig->getAttribute(Product::ENTITY, 'am_dropdown_attribute');

$options = $attribute->getSource()->getAllOptions();

foreach ($options as $option) {
    $id = $option['value'];
    $label = $option['label'];

    if ($id == '') {
        continue;
    }

    /** @var OptionSettingInterface $optionSetting */
    $optionSetting = $objectManager->create(OptionSettingInterface::class);

    $optionSetting->setAttributeCode($attribute->getAttributeCode());
    $optionSetting->setValue($id);
    $optionSetting->setMetaTitle($label);
    $optionSetting->setTitle($label);
    $optionSetting->setSliderPosition(0);
    $optionSetting->setIsShowInWidget(true);
    $optionSetting->setIsShowInSlider(true);
    $optionSetting->setStoreId(0);

    $optionSettingRepository->save($optionSetting);
}
