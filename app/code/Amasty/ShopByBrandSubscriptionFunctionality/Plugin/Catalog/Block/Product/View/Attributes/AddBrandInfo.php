<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand Subscription Functionality
 */

namespace Amasty\ShopByBrandSubscriptionFunctionality\Plugin\Catalog\Block\Product\View\Attributes;

use Amasty\ShopbyBrand\Model\ConfigProvider;
use Magento\Catalog\Block\Product\View\Attributes;
use Magento\Framework\View\LayoutInterface;

class AddBrandInfo
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LayoutInterface
     */
    private $layout;

    public function __construct(ConfigProvider $configProvider, LayoutInterface $layout)
    {
        $this->configProvider = $configProvider;
        $this->layout = $layout;
    }

    /**
     * @param Attributes $subject
     * @param array $result
     * @return array
     */
    public function afterGetAdditionalData(Attributes $subject, $result)
    {
        if ($subject->getNameInLayout() !== 'product.attributes') {
            return $result;
        }

        $indexAfter = array_search($this->configProvider->getBrandAttributeCode(), array_keys($result));
        if ($indexAfter === false) {
            return $result;
        }

        /** @var \Amasty\ShopByBrandSubscriptionFunctionality\Block\Product\BrandInfo $brandInfoBlock */
        $brandInfoBlock = $this->layout->getBlock('ambrand.brand_info.product_tab');
        if ($brandInfoBlock) {
            $brandInfoBlock->setProduct($subject->getProduct());
            $brandInfoHtml = $brandInfoBlock->toHtml();
            if (trim($brandInfoHtml)) {
                $result = array_slice($result, 0, $indexAfter + 1)
                    + ['amasty_brand_info' => ['label' => '', 'value' => $brandInfoHtml, 'code' => 'brand_info']]
                    + $result;
            }
        }

        return $result;
    }
}
