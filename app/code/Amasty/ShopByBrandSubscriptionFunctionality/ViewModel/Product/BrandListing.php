<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand Subscription Functionality
 */

namespace Amasty\ShopByBrandSubscriptionFunctionality\ViewModel\Product;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\ViewModel\OptionsDataBuilder;
use Amasty\ShopbyBrand\Model\ConfigProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class BrandListing implements ArgumentInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var OptionsDataBuilder
     */
    private $optionsDataBuilder;

    /**
     * @var OptionSettingInterface[]|null
     */
    private $brands;

    public function __construct(ConfigProvider $configProvider, OptionsDataBuilder $optionsDataBuilder)
    {
        $this->configProvider = $configProvider;
        $this->optionsDataBuilder = $optionsDataBuilder;
    }

    /**
     * @return OptionSettingInterface[]
     */
    public function getBrands(ProductInterface $product): array
    {
        if (!$this->brands) {
            $attributeValues = $this->optionsDataBuilder->getAttributeValues(
                $product,
                [$this->configProvider->getBrandAttributeCode()]
            );

            $this->brands = $this->optionsDataBuilder->getOptionSettingByValues($attributeValues)->getItems();
        }

        return $this->brands;
    }
}
