<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Block\Adminhtml\System\Config\Form\Field\Promo;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * @since 1.20.0
 */
class DynamicFields extends AbstractFieldArray implements PromoConfigInterface
{
    /**
     * @var array{
     *     'isIconVisible': bool,
     *     'iconBgColor': string,
     *     'iconSrc': string,
     *     'subscribeText': string,
     *     'promoLink': string|null,
     *     'comment': string|null
     * }
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

    /**
     * @var string
     */
    private string $moduleName;

    public function __construct(
        Context $context,
        SecureHtmlRenderer $secureRenderer,
        Manager $moduleManager,
        ConfigLabelRender $configLabelRender,
        string $moduleName,
        array $promoConfig = [],
        array $data = [],
        string $currentPromoConfig = self::PLAN_SUBSCRIBE
    ) {
        $this->promoConfig = array_merge(
            static::PROMO_CONFIGS[$currentPromoConfig],
            $promoConfig
        );
        $this->moduleManager = $moduleManager;
        $this->configLabelRender = $configLabelRender;
        $this->moduleName = $moduleName;

        parent::__construct($context, $data, $secureRenderer);
    }

    public function render(AbstractElement $element)
    {
        if ($this->isPromoNotActive()) {
            return parent::render($element);
        }

        $element->setDisabled(true);
        $element->setReadonly(true);
        if (isset($this->promoConfig[static::COMMENT_KEY])) {
            $element->setComment($this->promoConfig[static::COMMENT_KEY]);
        }

        $html = $this->configLabelRender->renderLabelHtml($element, $this->promoConfig);
        $html .= $this->_renderValue($element);
        $html .= $this->_renderHint($element);

        return $this->_decorateRowHtml($element, $html);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        if ($this->isPromoNotActive()) {
            return parent::_getElementHtml($element);
        }

        return '<div class="ampromo-config-dynamic-rows">' . parent::_getElementHtml($element) . '</div>';
    }

    private function isPromoNotActive(): bool
    {
        return $this->moduleManager->isEnabled($this->moduleName);
    }
}
