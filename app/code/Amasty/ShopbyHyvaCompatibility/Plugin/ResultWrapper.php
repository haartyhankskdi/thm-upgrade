<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop By Hyva Compatibility
 */

declare(strict_types=1);

namespace Amasty\ShopbyHyvaCompatibility\Plugin;

use Hyva\Theme\Service\CurrentTheme;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Page\Config;

class ResultWrapper
{
    /**
     * @var CurrentTheme
     */
    private $currentTheme;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param LayoutFactory $layoutFactory
     * @param CurrentTheme $currentTheme
     * @param Config $config
     */
    public function __construct(
        LayoutFactory $layoutFactory,
        CurrentTheme $currentTheme,
        Config $config
    ) {
        $this->currentTheme = $currentTheme;
        $this->layoutFactory = $layoutFactory;
        $this->config = $config;
    }

    /**
     * Processing block html after rendering
     *
     * @param AbstractBlock $subject
     * @param string $result
     * @return string
     * @throws LocalizedException
     */
    public function afterToHtml(AbstractBlock $subject, string $result): string
    {
        if ($subject->getResultCount() || $subject->getNameInLayout() !== 'search.result') {
            return $result;
        }

        $wrapperClass = $this->config->getPageLayout() === '1column' ? 'one-column-wrapper' : '';
        $hasShopByBlock = $subject->getLayout()->hasElement('category.amshopby.ajax');

        if ($this->currentTheme->isHyva() && $hasShopByBlock) {
            $result = sprintf(
                '<div id="amasty-shopby-product-list" class="relative" 
                    x-data="shopBy()" x-init="initShopBy()" 
                    x-bind="eventListeners" x-spread="eventListeners">%s%s</div>',
                $result,
                $this->getLoaderBlock()->toHtml()
            );
        } else {
            $result = sprintf('<div id="amasty-shopby-product-list" class="relative">%s</div>', $result);
        }

        return $wrapperClass ? sprintf('<div class="%s">%s</div>', $wrapperClass, $result) : $result;
    }

    /**
     * Loader Block
     *
     * @return Template
     */
    private function getLoaderBlock(): Template
    {
        return $this->layoutFactory->create()
            ->createBlock(Template::class)
            ->setTemplate('Amasty_ShopbyHyvaCompatibility::ui/loading.phtml');
    }
}
