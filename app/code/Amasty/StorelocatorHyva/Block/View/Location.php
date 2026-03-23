<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator Hyva Compatibility
 */

namespace Amasty\StorelocatorHyva\Block\View;

use Amasty\Storelocator\Block\View\Location as OriginalLocation;

class Location extends OriginalLocation
{
    public function getStorePageJsBlockHtml(): string
    {
        $originalTemplate = $this->getTemplate();
        $html = $this->setTemplate('Amasty_Storelocator::js/store-page.phtml')->toHtml();
        $this->setTemplate($originalTemplate);

        return $html;
    }
}
