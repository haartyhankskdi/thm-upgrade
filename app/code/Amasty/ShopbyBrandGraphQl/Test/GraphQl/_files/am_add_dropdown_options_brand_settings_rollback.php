<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Amasty Shop By Brand GraphQl for Magento 2 (System)
 */

use Amasty\ShopbyBase\Api\Data\OptionSettingRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;

$objectManager = Bootstrap::getObjectManager();

/** @var Config $eavConfig */
$eavConfig = $objectManager->get(Config::class);

/** @var Collection $options */
$options = $objectManager->create(Collection::class);

/** @var OptionSettingRepositoryInterface $optionSetting */
$optionSetting = $objectManager->create(OptionSettingRepositoryInterface::class);

$attribute = $eavConfig->getAttribute(Product::ENTITY, 'am_dropdown_attribute');
$options->setAttributeFilter($attribute->getId());
$optionIds = $options->getAllIds();

foreach ($optionIds as $id) {
    $optionSetting->deleteByOptionId($id);
}
