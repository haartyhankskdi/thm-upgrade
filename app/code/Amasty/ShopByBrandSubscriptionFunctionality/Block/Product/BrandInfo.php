<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand Subscription Functionality
 */

namespace Amasty\ShopByBrandSubscriptionFunctionality\Block\Product;

use Amasty\ShopbyBase\Model\OptionSetting;
use Amasty\ShopByBrandSubscriptionFunctionality\ViewModel\BrandInfo as BrandInfoVideModel;
use Amasty\ShopByBrandSubscriptionFunctionality\ViewModel\Product\BrandListing as BrandListingVideModel;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;

class BrandInfo extends Template implements IdentityInterface
{
    /**
     * @var ProductInterface|null $product
     */
    private $product;

    protected function _construct(): void
    {
        parent::_construct();

        if (!$this->hasData('cache_lifetime')) {
            $this->setData('cache_lifetime', 86400);
        }
    }

    public function getIdentities(): array
    {
        if (!$this->getProduct()) {
            return [];
        }

        $identities = [];
        foreach ($this->getBrandListingViewModel()->getBrands($this->getProduct()) as $brand) {
            $identities[] = OptionSetting::CACHE_TAG . '_' . $brand->getValue();
        }

        return $identities;
    }

    protected function getCacheTags(): array
    {
        if (!$this->getProduct()) {
            return [];
        }

        $tags = parent::getCacheTags();
        foreach ($this->getBrandListingViewModel()->getBrands($this->getProduct()) as $brand) {
            $tags[] = OptionSetting::CACHE_TAG . '_' . $brand->getValue();
        }

        return $tags;
    }

    public function getCacheKeyInfo(): array
    {
        $parts = parent::getCacheKeyInfo();
        foreach ($this->getBrandListingViewModel()->getBrands($this->getProduct()) as $brand) {
            $parts[] = OptionSetting::CACHE_TAG . '_' . $brand->getValue();
        }

        return  $parts;
    }

    public function getBrandInfoViewModel(): BrandInfoVideModel
    {
        return $this->getData('brand_info_view_model');
    }

    public function getBrandListingViewModel(): BrandListingVideModel
    {
        return $this->getData('brand_listing_view_model');
    }

    public function setProduct(ProductInterface $product): void
    {
        $this->product = $product;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }
}
