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
 * @category    Mageplaza
 * @package     ${MODULENAME}
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FrequentlyBought\Block\Product\View\Type;

use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle as CatalogBundle;
use Magento\Bundle\Model\Product\PriceFactory;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Block\Product\Context;
use Magento\CatalogRule\Model\ResourceModel\Product\CollectionProcessor;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Stdlib\ArrayUtils;

/**
 * Class Bundle
 * @package Mageplaza\FrequentlyBought\Block\Product\View\Type
 */
class Bundle extends CatalogBundle
{

    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        \Magento\Catalog\Helper\Product $catalogProduct,
        PriceFactory $productPrice,
        EncoderInterface $jsonEncoder,
        FormatInterface $localeFormat,
        array $data = [],
        ?CollectionProcessor $catalogRuleProcessor = null
    )
    {
        parent::__construct(
            $context,
            $arrayUtils,
            $catalogProduct,
            $productPrice,
            $jsonEncoder,
            $localeFormat,
            $data,
            $catalogRuleProcessor
        );
        $this->catalogRuleProcessor = $catalogRuleProcessor ?? ObjectManager::getInstance()
            ->get(CollectionProcessor::class);
    }

    /**
     * @return array
     */
    public function getBundleOptions()
    {
        $this->options = null;

        return $this->getOptions();
    }

    public function getOptions($stripSelection = false)
    {
        $product = $this->getProduct();
        /** @var Type $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);

        $optionCollection = $typeInstance->getOptionsCollection($product);

        $selectionCollection = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($product),
            $product
        );
        $this->catalogRuleProcessor->addPriceData($selectionCollection);
        $selectionCollection->addTierPriceData();

        $this->options = $optionCollection->appendSelections(
            $selectionCollection,
            $stripSelection,
            $this->catalogProduct->getSkipSaleableCheck()
        );

        return $this->options;
    }
}
