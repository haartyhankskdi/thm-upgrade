<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop By Hyva Compatibility
 */

declare(strict_types=1);

namespace Amasty\ShopbyHyvaCompatibility\ViewModel;

use Amasty\Shopby\Model\ConfigProvider;
use Amasty\Shopby\Model\Layer\FilterList;
use Amasty\Shopby\Model\Layer\GetFiltersExpanded;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class JsInit implements ArgumentInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;
    /**
     * @var Layer
     */
    private $catalogLayer;

    /**
     * @var FilterList
     */
    private $filterList;

    /**
     * @var GetFiltersExpanded
     */
    private $getFiltersExpanded;

    /**
     * @param ConfigProvider $configProvider
     * @param LayerResolver $layerResolver
     * @param FilterList $filterList
     * @param GetFiltersExpanded $getFiltersExpanded
     */
    public function __construct(
        ConfigProvider $configProvider,
        LayerResolver $layerResolver,
        FilterList $filterList,
        GetFiltersExpanded $getFiltersExpanded
    ) {
        $this->configProvider = $configProvider;
        $this->catalogLayer = $layerResolver->get();
        $this->filterList = $filterList;
        $this->getFiltersExpanded = $getFiltersExpanded;
    }

    /**
     * @deprecated the method has been renamed
     * @see \Amasty\ShopbyHyvaCompatibility\ViewModel\JsInit::isEnableStickySidebarDesktop
     */
    public function getEnableStickySidebarDesktop(): bool
    {
        return $this->configProvider->isEnableStickySidebarDesktop();
    }

    /**
     * Check is sticky-sidebar enabled
     *
     * @return bool
     */
    public function isEnableStickySidebarDesktop(): bool
    {
        return $this->configProvider->isEnableStickySidebarDesktop();
    }

    /**
     * Check is Single Choice Mode enabled
     *
     * @return bool
     */
    public function isSingleChoiceMode(): bool
    {
        return $this->configProvider->isSingleChoiceMode();
    }

    /**
     * Get filters expanded
     *
     * @return array
     */
    public function getFiltersExpanded(): array
    {
        return $this->getFiltersExpanded->execute($this->filterList->getFilters($this->catalogLayer));
    }

    /**
     * Check is ajax enabled
     *
     * @return bool
     */
    public function isAjaxSettingEnabled(): bool
    {
        return $this->configProvider->isAjaxEnabled();
    }
}
