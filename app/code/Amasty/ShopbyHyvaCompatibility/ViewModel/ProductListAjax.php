<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop By Hyva Compatibility
 */

declare(strict_types=1);

namespace Amasty\ShopbyHyvaCompatibility\ViewModel;

use Magento\Framework\Registry;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Amasty\Shopby\Helper\Data;
use Amasty\Shopby\Model\Layer\FilterList;
use Amasty\Shopby\Model\Config\MobileConfigResolver;
use Amasty\Shopby\Block\Product\ProductList\Ajax;
use Amasty\ShopbyBase\Model\Detection\MobileDetect;

class ProductListAjax implements ArgumentInterface
{
    public const CACHE_TAG = 'client_';

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    private $layer;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ToolbarMemorizer
     */
    private $toolbarMemorizer;

    /**
     * @var Toolbar
     */
    private $toolbar;

    /**
     * @var MobileDetect
     */
    private $mobileDetect;

    /**
     * @var Config $config
     */
    protected $config;

    /**
     * @var MobileConfigResolver
     */
    private $mobileConfigResolver;

    /**
     * @var Ajax
     */
    private $ajaxBlock;

    /**
     * @param Resolver $layerResolver
     * @param Data $helper
     * @param Registry $registry
     * @param ToolbarMemorizer $toolbarMemorizer
     * @param MobileDetect $mobileDetect
     * @param Toolbar $toolbar
     * @param Config $config
     * @param MobileConfigResolver $mobileConfigResolver
     * @param Ajax $ajaxBlock
     */
    public function __construct(
        Resolver             $layerResolver,
        Data                 $helper,
        Registry             $registry,
        ToolbarMemorizer     $toolbarMemorizer,
        MobileDetect         $mobileDetect,
        Toolbar              $toolbar,
        Config               $config,
        MobileConfigResolver $mobileConfigResolver,
        Ajax                 $ajaxBlock
    ) {
        $this->layer = $layerResolver->get();
        $this->helper = $helper;
        $this->registry = $registry;
        $this->toolbarMemorizer = $toolbarMemorizer;
        $this->mobileDetect = $mobileDetect;
        $this->toolbar = $toolbar;
        $this->config = $config;
        $this->mobileConfigResolver = $mobileConfigResolver;
        $this->ajaxBlock = $ajaxBlock;
    }

    /**
     * Get is GTM enabled
     *
     * @return bool
     */
    public function isGoogleTagManager(): bool
    {
        return $this->ajaxBlock->isGoogleTagManager();
    }

    /**
     * Get is Ajax enabled
     *
     * @return bool
     */
    public function canShowBlock(): bool
    {
        return $this->mobileConfigResolver->isAjaxEnabled();
    }

    /**
     * Get is Setting Ajax enabled
     *
     * @return bool
     */
    public function isAjaxSettingEnabled(): bool
    {
        return $this->ajaxBlock->canShowBlock();
    }

    /**
     * Get is select by click enabled
     *
     * @return int
     */
    public function submitByClick(): int
    {
        return $this->mobileConfigResolver->getSubmitFilterMode();
    }

    /**
     * Check is mobile
     *
     * @return bool
     */
    public function isMobile(): bool
    {
        return $this->mobileDetect->isMobile();
    }

    /**
     * Get view mode
     *
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->mobileDetect->isMobile() ? 'mobile' : 'desktop'];
    }

    /**
     * Get is scroll up enabled
     *
     * @return int
     */
    public function scrollUp(): int
    {
        return $this->ajaxBlock->scrollUp();
    }

    /**
     * Retrieve active filters
     *
     * @return array
     */
    protected function getActiveFilters(): array
    {
        $filters = $this->layer->getState()->getFilters();
        if (!is_array($filters)) {
            $filters = [];
        }
        return $filters;
    }

    /**
     * Retrieve Clear Filters URL
     *
     * @return string
     */
    public function getClearUrl(): string
    {
        return $this->helper->getAjaxCleanUrl($this->getActiveFilters());
    }

    /**
     * Get current category ID
     *
     * @return int
     */
    public function getCurrentCategoryId(): int
    {
        return (int) $this->helper->getCurrentCategory()->getId();
    }

    /**
     * Get is only obe category can be selected
     *
     * @return int
     */
    public function isCategorySingleSelect(): int
    {
        $allFilters = $this->registry->registry(FilterList::ALL_FILTERS_KEY, []);
        foreach ($allFilters as $filter) {
            if ($filter instanceof \Amasty\Shopby\Model\Layer\Filter\Category) {
                return (int) !$filter->isMultiselect();
            }
        }

        return 0;
    }

    /**
     * Get config
     *
     * @param string $path
     * @return string
     */
    public function getConfig($path): string
    {
        return $this->ajaxBlock->getConfig($path);
    }

    /**
     * Get GTM acc ID
     *
     * @return string
     */
    public function getGtmAccountId(): string
    {
        return $this->ajaxBlock->getGtmAccountId();
    }

    /**
     * Get is memorizing enabled for toolbar
     *
     * @return int
     */
    public function isMemorizingAllowed(): int
    {
        return (int) $this->toolbarMemorizer->isMemorizingAllowed();
    }

    /**
     * Retrieve widget options in json format
     *
     * @param array $customOptions Optional parameter for passing custom selectors from template
     * @return string
     */
    public function getToolbarWidgetOptions(array $customOptions = []): string
    {
        return $this->toolbar->getWidgetOptionsJson($customOptions);
    }

    /**
     * Get grouped product value
     *
     * @param int $attributeId
     * @param string $groupCode
     * @return string $value
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getGroupsByAttributeId(int $attributeId, string $groupCode): string
    {
        return '';
    }

    /**
     * Retrieve the appropriate page layout.
     *
     * @return string
     */
    public function getPageLayout(): string
    {
        return $this->config->getPageLayout() ?? '';
    }

    /**
     * Check is set one column layout
     *
     * @return bool
     */
    public function isOneColumn(): bool
    {
        $pageLayout = $this->getPageLayout();
        return $pageLayout === '1column' || $pageLayout === 'cms-full-width' || $pageLayout === 'product-full-width';
    }
}
