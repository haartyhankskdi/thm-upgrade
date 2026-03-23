<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Cookie Hyva Compatibility (System)
 */

namespace Amasty\GdprCookieHyva\ViewModel;

use Amasty\GdprCookie\Model\ConfigProvider;
use Hyva\Theme\Model\Modal\ModalBuilderInterface;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * @method bool getOverlayEnabled()
 * @method string|null getOverlayClasses()
 * @method string getContainerClasses()
 * @method string getDialogClasses()
 * @method string|null getBarClasses()
 * @method array|null getConfigModifiers()
 * @method string|null getBarAriaLabel()
 */
class CookieBar extends DataObject implements ArgumentInterface
{
    public const REF_NAME = 'am-cookie-bar';

    /**
     * @var ModalBuilderInterface
     */
    private $modal;

    /**
     * @var AbstractBlock
     */
    private $block;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider,
        array          $data = []
    ) {
        $this->configProvider = $configProvider;
        parent::__construct($data);
    }

    public function getBar($isAllowCustomersCloseBar): ModalBuilderInterface
    {
        $bar = $this->modal
            ->withTemplate($this->block->getTemplate())
            ->withDialogRefName(self::REF_NAME);
        $bar->withAriaLabel($this->getBarAriaLabel() ?? __('Cookie Bar')->render());

        $this->getContainerClasses() && $bar->withContainerClasses($this->getContainerClasses());
        $this->getDialogClasses() && $bar->withDialogClasses($this->getDialogClasses());
        $this->getOverlayClasses() && $bar->withOverlayClasses($this->getOverlayClasses());
        $this->getOverlayEnabled() || !$isAllowCustomersCloseBar ? $bar->overlayEnabled() : $bar->overlayDisabled();

        $contentRenderer = $bar->getContentRenderer();
        $contentRenderer->setData($this->block->getData());

        foreach ($this->block->getChildNames() as $childName) {
            $childBlock = $this->block->getChildBlock($childName);
            $childBlock && $contentRenderer->setChild($childName, $childBlock);
        }

        return $bar;
    }

    public function addData(array $arr): CookieBar
    {
        parent::addData($arr);

        if ($modifiers = $this->getConfigModifiers()) {
            foreach ($modifiers as $configMethod => $configValues) {
                $configValue = $this->configProvider->{$configMethod}();
                $dataToAdd = $configValues[$configValue] ?? [];

                foreach ($dataToAdd as $dataKey => $dataValue) {
                    $this->setData($dataKey, $this->getData($dataKey) . ' ' . $dataValue);
                }
            }
        }

        return $this;
    }

    public function setBlock(AbstractBlock $block): void
    {
        $this->block = $block;
    }

    public function setModal(ModalBuilderInterface $modal): void
    {
        $this->modal = $modal;
    }

    public function isCookieBarEnabled(): bool
    {
        return $this->configProvider->isCookieBarEnabled();
    }
}
