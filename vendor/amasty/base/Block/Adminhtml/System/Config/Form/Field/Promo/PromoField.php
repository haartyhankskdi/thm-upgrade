<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Block\Adminhtml\System\Config\Form\Field\Promo;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager;
use Magento\Framework\Escaper;
use Magento\Framework\View\Asset\Repository as AssetRepository;

class PromoField extends Field implements PromoConfigInterface
{
    /**
     * @var string
     */
    private string $moduleName;

    /**
     * @var array{
     *      'isIconVisible': bool,
     *      'iconBgColor': string,
     *      'iconSrc': string,
     *      'subscribeText': string,
     *      'promoLink': string|null,
     *      'comment': string|null
     *  }
     */
    private array $promoConfig;

    /**
     * @var Manager
     */
    private Manager $moduleManager;

    /**
     * @var ConfigLabelRender
     */
    private ConfigLabelRender $configLabelRender;

    public function __construct(
        Context $context,
        Manager $moduleManager,
        ?Escaper $escaper, // @deprecated since 1.20.0
        ?AssetRepository $assetRepository, // @deprecated since 1.20.0
        string $moduleName,
        array $promoConfig = [],
        array $data = [],
        string $currentPromoConfig = self::PLAN_SUBSCRIBE,
        ?ConfigLabelRender $configLabelRender = null // TODO move to not optional
    ) {
        $this->moduleName = $moduleName;
        $this->moduleManager = $moduleManager;
        $this->promoConfig = array_merge(
            static::PROMO_CONFIGS[$currentPromoConfig],
            $promoConfig
        );
        // OM for backward compatibility
        $this->configLabelRender = $configLabelRender ?? ObjectManager::getInstance()->get(ConfigLabelRender::class);
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if ($this->moduleManager->isEnabled($this->moduleName)) {
            return parent::render($element);
        }

        $element->setDisabled(true);
        $element->setReadonly(true);
        if (isset($this->promoConfig[static::COMMENT_KEY])) {
            $element->setComment($this->promoConfig[static::COMMENT_KEY]);
        }

        $html = $this->renderLabel($element);
        $html .= $this->_renderValue($element);
        $html .= $this->_renderHint($element);

        if ($element->getData('ext_type') === 'multiple') {
            $html = str_replace('<select', '<select readonly="1"', $html);
        }

        return $this->_decorateRowHtml($element, $html);
    }

    /**
     * @deprecated since 1.20.0
     * @see ConfigLabelRender::renderLabelHtml
     */
    public function renderLabel(AbstractElement $element): string
    {
        return $this->configLabelRender->renderLabelHtml($element, $this->promoConfig);
    }
}
