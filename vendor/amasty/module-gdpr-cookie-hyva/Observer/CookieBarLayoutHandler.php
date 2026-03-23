<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Cookie Hyva Compatibility (System)
 */

namespace Amasty\GdprCookieHyva\Observer;

use Amasty\GdprCookieHyva\Model\Layout\CookieBarLayoutResolver;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout;

class CookieBarLayoutHandler implements ObserverInterface
{
    /**
     * @var CookieBarLayoutResolver
     */
    private $layoutResolver;

    public function __construct(
        CookieBarLayoutResolver $layoutResolver
    ) {
        $this->layoutResolver = $layoutResolver;
    }

    public function execute(Observer $observer): void
    {
        /** @var Layout $layout */
        $layout = $observer->getData('layout');

        $this->layoutResolver->resolve($layout);
    }
}
