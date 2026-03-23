<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand Subscription Functionality
 */

namespace Amasty\ShopByBrandSubscriptionFunctionality\Observer\Frontend;

use Amasty\ShopbyBrand\Model\BrandResolver;
use Amasty\ShopByBrandSubscriptionFunctionality\Model\Source\PageLayout as PageLayoutSource;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class UpdateBrandPageLayout implements ObserverInterface
{
    /**
     * @var BrandResolver
     */
    private $brandResolver;

    public function __construct(BrandResolver $brandResolver)
    {
        $this->brandResolver = $brandResolver;
    }

    /**
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var DataObject $designSettings */
        $designSettings = $observer->getData('design_settings');
        if (!$designSettings) {
            return;
        }

        $brand = $this->brandResolver->getCurrentBrand();
        if ($brand
            && $brand->getPageLayout()
            && $brand->getPageLayout() !== PageLayoutSource::DEFAULT
        ) {
            $designSettings->setPageLayout($brand->getPageLayout());
        }
    }
}
