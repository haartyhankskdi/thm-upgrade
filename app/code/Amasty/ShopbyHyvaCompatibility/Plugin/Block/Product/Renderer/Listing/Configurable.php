<?php declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop By Hyva Compatibility
 */

namespace Amasty\ShopbyHyvaCompatibility\Plugin\Block\Product\Renderer\Listing;

use Hyva\Theme\Service\CurrentTheme;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutFactory;
use Magento\Swatches\Block\Product\Renderer\Listing\Configurable as ConfigurableOrigin;

class Configurable
{
    public const NAME_LAYOUT_CONFIGURABLE = 'category.product.type.details.renderers.configurable';
    /** @var CurrentTheme */
    private CurrentTheme $currentTheme;

    /** @var LayoutFactory */
    private LayoutFactory $layoutFactory;

    /** @var string */
    private string $templateFilter;

    /**
     * @param LayoutFactory $layoutFactory
     * @param CurrentTheme $currentTheme
     * @param string $templateFilter
     */
    public function __construct(
        LayoutFactory $layoutFactory,
        CurrentTheme  $currentTheme,
        string $templateFilter
    ) {
        $this->currentTheme = $currentTheme;
        $this->layoutFactory = $layoutFactory;
        $this->templateFilter = $templateFilter;
    }

    /**
     * Processing block html after rendering
     *
     * @param ConfigurableOrigin $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml(ConfigurableOrigin $subject, string $result): string
    {
        if ($subject->getNameInLayout() !== self::NAME_LAYOUT_CONFIGURABLE) {
            return $result;
        }

        if ($this->currentTheme->isHyva()) {
            $result = str_replace(
                'x-data="initConfigurableSwatchOptions_',
                'x-data="initAmInitConfigurableSwatchOptions_',
                $result
            );
            return $result . $this->getMixinConfigurableRenderers((string) $subject->getProduct()->getId())->toHtml();
        }

        return $result;
    }

    /**
     * Get mixin configurable renderers
     *
     * @param string $productId
     * @return Template
     */
    private function getMixinConfigurableRenderers(string $productId): Template
    {
        return $this->layoutFactory
            ->create()
            ->createBlock(Template::class)
            ->setTemplate($this->templateFilter)
            ->setProductId($productId);
    }
}
