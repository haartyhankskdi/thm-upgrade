<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_FrequentlyBought
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FrequentlyBought\Block\Product\View\Type;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\SwatchAttributesProvider;
use Mageplaza\FrequentlyBought\Helper\Data as FrequentlyBoughtHelper;

/**
 * Class Configurable
 *
 * @package Mageplaza\FrequentlyBought\Block\Product\View\Type
 */
class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
    const MAGEPLAZA_FREQUENTLY_BOUGHT_RENDERER_TEMPLATE = 'Mageplaza_FrequentlyBought::product/view/type/options/configurable.phtml';
    const MAGEPLAZA_HYVA_FREQUENTLY_BOUGHT_RENDERER_TEMPLATE = 'Mageplaza_FrequentlyBought::hyva/product/view/options/configurable.phtml';
    /**
     * @var FrequentlyBoughtHelper
     */
    protected $frequentlyBoughtHelper;

    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        Data $helper,
        CatalogProduct $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        SwatchData $swatchHelper,
        Media $swatchMediaHelper,
        FrequentlyBoughtHelper $frequentlyBoughtHelper,
        array $data = [],
        ?SwatchAttributesProvider $swatchAttributesProvider = null,
        ?UrlBuilder $imageUrlBuilder = null
    ) {
        $this->frequentlyBoughtHelper = $frequentlyBoughtHelper;
        parent::__construct($context, $arrayUtils, $jsonEncoder, $helper, $catalogProduct, $currentCustomer,
            $priceCurrency, $configurableAttributeData, $swatchHelper, $swatchMediaHelper, $data,
            $swatchAttributesProvider, $imageUrlBuilder);
    }

    /**
     * @return string
     */
    protected function getRendererTemplate()
    {
        if($this->frequentlyBoughtHelper->checkHyvaTheme()) {
            return self::MAGEPLAZA_HYVA_FREQUENTLY_BOUGHT_RENDERER_TEMPLATE;

        }
        return self::MAGEPLAZA_FREQUENTLY_BOUGHT_RENDERER_TEMPLATE;
    }
}
