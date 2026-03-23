<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Hyva Compatibility M2 by Amasty
 */

declare(strict_types=1);

namespace Amasty\XsearchHyvaCompatibility\ViewModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Template;

class ComponentsRegistry implements ArgumentInterface
{
    /** @var array  */
    private array $components = [];

    /** @var array  */
    private array $renderedPool = [];

    /** @var Template  */
    private Template $block;

    /**
     * @param Template $block
     * @param array $components
     */
    public function __construct(
        Template $block,
        array $components = []
    ) {
        $this->components = $components;
        $this->block = $block;
    }

    /**
     * Retrieve all components
     *
     * @return array
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * Render component only once
     *
     * @param string $componentName
     * @return string
     * @throws LocalizedException
     */
    public function renderOnce(string $componentName): string
    {
        if (isset($this->renderedPool[$componentName])) {
            return '';
        }

        return $this->render($componentName);
    }

    /**
     * Render component by name
     *
     * @param string $componentName
     * @return string
     * @throws LocalizedException
     */
    public function render(string $componentName): string
    {
        if (empty($this->components[$componentName])) {
            throw new LocalizedException(
                __('Component %1 is not registered', $componentName)
            );
        }

        $component = $this->components[$componentName];
        $this->block->setTemplate($component['template']);
        $this->renderedPool[$componentName] = true;

        return $this->block->toHtml();
    }
}
