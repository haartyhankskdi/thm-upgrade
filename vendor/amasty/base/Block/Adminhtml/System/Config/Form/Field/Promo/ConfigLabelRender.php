<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Block\Adminhtml\System\Config\Form\Field\Promo;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Escaper;
use Magento\Framework\View\Asset\Repository as AssetRepository;

/**
 * @since 1.20.0
 */
class ConfigLabelRender
{
    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @var AssetRepository
     */
    private AssetRepository $assetRepository;

    public function __construct(Escaper $escaper, AssetRepository $assetRepository)
    {
        $this->escaper = $escaper;
        $this->assetRepository = $assetRepository;
    }

    public function renderLabelHtml(AbstractElement $element, array $config): string
    {
        return <<<LABEL
            <td class="label">
                <div class="ampromo-config-label">
                    {$this->getIconHtml($config)}
                    <div class="ampromo-config-content-container">
                        <div>
                            <label for="{$element->getHtmlId()}">
                                <span>
                                    {$element->getLabel()}
                                </span>
                            </label>
                        </div>
                        <div class="ampromo-config-notification-message">
                            {$this->escaper->escapeHtml(__($config[PromoConfigInterface::SUBSCRIBE_TEXT_KEY]))}
                        </div>
                    </div>
                </div>
            </td>
        LABEL;
    }

    public function getIconHtml(array $config): string
    {
        if ($config[PromoConfigInterface::IS_ICON_VISIBLE_KEY] === false) {
            return '';
        }

        $icon = <<<ICON
            <span class="ampromo-config-icon"
                style="
                    background-color: {$config[PromoConfigInterface::ICON_BG_COLOR_KEY]};
                    background-image: url('{$this->getIconUrl($config[PromoConfigInterface::ICON_SRC_KEY])}');
                "></span>
        ICON;

        if (!$config[PromoConfigInterface::PROMO_LINK_KEY]) {
            return $icon;
        }

        return <<<LINK
            <a href="{$config[PromoConfigInterface::PROMO_LINK_KEY]}" target="_blank">
                {$icon}
            </a>
        LINK;
    }

    private function getIconUrl(string $src): string
    {
        return $this->escaper->escapeUrl($this->assetRepository->getUrl($src));
    }
}
