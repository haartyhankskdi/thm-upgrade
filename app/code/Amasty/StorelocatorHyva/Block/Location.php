<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator Hyva Compatibility
 */

namespace Amasty\StorelocatorHyva\Block;

use Amasty\Storelocator\Block\Location as OriginalLocation;

class Location extends OriginalLocation
{
    public function getJsBlockHtml(): string
    {
        $originalTemplate = $this->getTemplate();
        $html = $this->setTemplate('Amasty_Storelocator::js/store-locator.phtml')
        ->setData('googleMapId', $this->getMapId())
        ->setData('cache_lifetime', null)
        ->toHtml();
        $this->setTemplate($originalTemplate);

        return $html;
    }

    public function getSearchHtml(): string
    {
        $originalTemplate = $this->getTemplate();
        $html = $this->setTemplate('Amasty_Storelocator::search.phtml')->toHtml();
        $this->setTemplate($originalTemplate);

        return $html;
    }

    public function getIsFilterOpen(): int
    {
        return $this->configProvider->getCollapseFilter() ? 0 : 1;
    }
}
