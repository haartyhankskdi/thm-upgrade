<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Base for Magento 2 (System)
 */

namespace Amasty\ShopbyBase\Observer;

use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ToolbarMemorizerObserver implements ObserverInterface
{
    /**
     * @var ToolbarMemorizer
     */
    private ToolbarMemorizer $toolbarMemorizer;

    public function __construct(ToolbarMemorizer $toolbarMemorizer)
    {
        $this->toolbarMemorizer = $toolbarMemorizer;
    }

    /**
     * Save toolbar parameters in catalog session.
     *
     * @param Observer $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer): void
    {
        $this->toolbarMemorizer->memorizeParams();
    }
}
