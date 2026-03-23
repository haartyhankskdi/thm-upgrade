<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand Subscription Functionality
 */

namespace Amasty\ShopByBrandSubscriptionFunctionality\ViewModel;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBrand\Model\BrandResolver;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class BrandInfo implements ArgumentInterface
{
    /**
     * @var BrandResolver
     */
    private $brandResolver;

    /**
     * @var string
     */
    private $position;

    public function __construct(BrandResolver $brandResolver, string $position)
    {
        $this->position = $position;
        $this->brandResolver = $brandResolver;
    }

    public function isShow(?OptionSettingInterface $brand = null): bool
    {
        $brand = $brand ?? $this->getCurrentBrand();

        return $brand
            && $brand->isShowBrandInfo()
            && $this->isCurrentPosition($brand)
            && count($this->getBrandInfoValues($brand));
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function getCurrentBrand(): ?OptionSettingInterface
    {
        return $this->brandResolver->getCurrentBrand();
    }

    /**
     * @return string[]
     */
    public function getBrandInfoValues(?OptionSettingInterface $brand = null): array
    {
        $brand = $brand ?: $this->getCurrentBrand();
        if (!$brand) {
            return [];
        }

        return array_filter([
            $brand->getBrandInfoPostalAddress(),
            $brand->getBrandInfoElectronicAddress(),
            $brand->getBrandInfoContact()
        ], static function (?string $value) {
            return $value !== null && !empty(trim($value));
        });
    }

    private function isCurrentPosition(OptionSettingInterface $brand): bool
    {
        $brandInfoPositionBlock = $brand->getBrandInfoBlockPosition();
        if (!$brandInfoPositionBlock) {
            return false;
        }

        return in_array(
            $this->position,
            explode(',', $brandInfoPositionBlock),
            true
        );
    }
}
