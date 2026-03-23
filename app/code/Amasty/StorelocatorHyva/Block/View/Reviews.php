<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator Hyva Compatibility
 */

namespace Amasty\StorelocatorHyva\Block\View;

use Amasty\Storelocator\Block\View\Reviews as OriginalReviews;

class Reviews extends OriginalReviews
{
    public function getReviewsStarsHtml($rating): string
    {
        $originalTemplate = $this->getTemplate();
        $html = $this->setTemplate('Amasty_Storelocator::helper/review_stars.phtml')
        ->setData('rating', $rating)
        ->toHtml();
        $this->setTemplate($originalTemplate);

        return $html;
    }
}
