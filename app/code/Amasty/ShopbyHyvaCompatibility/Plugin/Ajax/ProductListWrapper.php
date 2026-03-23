<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop By Hyva Compatibility
 */

declare(strict_types=1);

namespace Amasty\ShopbyHyvaCompatibility\Plugin\Ajax;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutFactory;
use Hyva\Theme\Service\CurrentTheme;
use Magento\Framework\View\Page\Config;

class ProductListWrapper
{
    /** @var CurrentTheme  */
    private $currentTheme;

    /** @var Http */
    private $request;

    /** @var LayoutFactory  */
    private $layoutFactory;

    /**  @var Config */
    protected $config;

    /**
     * @param Http $request
     * @param LayoutFactory $layoutFactory
     * @param CurrentTheme $currentTheme
     * @param Config $config
     */
    public function __construct(
        Http $request,
        LayoutFactory $layoutFactory,
        CurrentTheme $currentTheme,
        Config $config
    ) {
        $this->currentTheme = $currentTheme;
        $this->request = $request;
        $this->layoutFactory = $layoutFactory;
        $this->config = $config;
    }

    /**
     * Processing block html after rendering
     *
     * @param ListProduct $subject
     * @param string $result
     * @return string
     * @throws LocalizedException
     */
    public function afterToHtml(ListProduct $subject, string $result): string
    {
        if ($this->isNotValidRequest() ||
            !in_array($subject->getNameInLayout(), ['category.products.list', 'search_result_list'], true)) {
            return $result;
        }

        if ($this->currentTheme->isHyva()) {
            $hasShopByBlock = $subject->getLayout()->hasElement('category.amshopby.ajax');
            $divWrapper = ($this->config->getPageLayout() === '1column')
                ? '<div class="one-column-wrapper">%s</div>' : '%s';

            if ($hasShopByBlock) {
                return sprintf(
                    $divWrapper,
                    sprintf(
                        '<div id="amasty-shopby-product-list" 
                        class="relative"
                        x-data="shopBy()"
                        x-init="initShopBy()"
                        x-bind="eventListeners"
                        x-spread="eventListeners">%s%s</div>',
                        $result,
                        $this->getLoaderBlock()->toHtml()
                    )
                );
            }
        }

        return sprintf('<div id="amasty-shopby-product-list" class="relative">%s</div>', $result);
    }

    /**
     * Loader Block
     *
     * @return Template
     */
    private function getLoaderBlock(): Template
    {
        return $this->layoutFactory
            ->create()
            ->createBlock(Template::class)
            ->setTemplate('Amasty_ShopbyHyvaCompatibility::ui/loading.phtml');
    }

    /**
     * Check if request is valid
     *
     * @return bool
     */
    private function isNotValidRequest(): bool
    {
        return (
            $this->request->getFullActionName() === 'catalogsearch_advanced_result'
                || $this->request->getParam('is_scroll')
        );
    }
}
