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
 * @package     Mageplaza_FrequentlyBought
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FrequentlyBought\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Ui\Component\Listing\Columns\Price;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Related;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Fieldset;
use Mageplaza\FrequentlyBought\Api\Data\ProductLinkInterface;
use Mageplaza\FrequentlyBought\Api\FrequentlyBoughtRepositoryInterface;
use Mageplaza\FrequentlyBought\Helper\Data;

/**
 * Class FrequentlyBought
 * @package Mageplaza\FrequentlyBought\Ui\DataProvider\Product\Form\Modifier
 */
class FrequentlyBought extends Related
{
    const DATA_SCOPE_FBT = 'fbt';

    /**
     * @var string
     */
    private static $previousGroup = 'search-engine-optimization';

    /**
     * @var int
     */
    private static $sortOrder = 90;

    /**
     * @var Price
     */
    protected $priceComponent;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var FrequentlyBoughtRepositoryInterface
     */
    protected $frequentlyBoughtRepository;

    /**
     * FrequentlyBought constructor.
     *
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param ProductLinkRepositoryInterface $productLinkRepository
     * @param ProductRepositoryInterface $productRepository
     * @param ImageHelper $imageHelper
     * @param Status $status
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param Data $helperData
     * @param FrequentlyBoughtRepositoryInterface $frequentlyBoughtRepository
     * @param string $scopeName
     * @param string $scopePrefix
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        ProductLinkRepositoryInterface $productLinkRepository,
        ProductRepositoryInterface $productRepository,
        ImageHelper $imageHelper,
        Status $status,
        AttributeSetRepositoryInterface $attributeSetRepository,
        Data $helperData,
        FrequentlyBoughtRepositoryInterface $frequentlyBoughtRepository,
        $scopeName = '',
        $scopePrefix = ''
    ) {
        parent::__construct(
            $locator,
            $urlBuilder,
            $productLinkRepository,
            $productRepository,
            $imageHelper,
            $status,
            $attributeSetRepository,
            $scopeName,
            $scopePrefix
        );
        $this->helperData = $helperData;
        $this->frequentlyBoughtRepository = $frequentlyBoughtRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if (!$this->helperData->isEnabled($this->locator->getStore()->getId())) {
            return parent::modifyMeta($meta);
        }

        $meta = array_replace_recursive(
            $meta,
            [
                static::GROUP_RELATED => [
                    'children' => [
                        $this->scopePrefix . static::DATA_SCOPE_RELATED => $this->getRelatedFieldset(),
                        $this->scopePrefix . static::DATA_SCOPE_UPSELL => $this->getUpSellFieldset(),
                        $this->scopePrefix . static::DATA_SCOPE_CROSSSELL => $this->getCrossSellFieldset(),
                        $this->scopePrefix . static::DATA_SCOPE_FBT => $this->getFbtFieldset(),
                    ],
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Related Products, Up-Sells, Cross-Sells and Frequently Bought Together'),
                                'collapsible' => true,
                                'componentType' => Fieldset::NAME,
                                'dataScope' => static::DATA_SCOPE,
                                'sortOrder' => $this->getNextGroupSortOrder(
                                    $meta,
                                    self::$previousGroup,
                                    self::$sortOrder
                                ),
                            ],
                        ],

                    ],
                ],
            ]
        );

        return $meta;
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function modifyData(array $data)
    {
        if (!$this->helperData->isEnabled($this->locator->getStore()->getId())) {
            return parent::modifyData($data);
        }

        $data = parent::modifyData($data);
        /** @var Product $product */
        $product = $this->locator->getProduct();
        $productId = $product->getId();

        if (!$productId) {
            return $data;
        }
        if (!$this->helperData->hasProductLinks($productId)) {
            return $data;
        }
        foreach ($this->frequentlyBoughtRepository->getList($product->getSku()) as $productLink) {
            /** @var Product $linkedProduct */
            $linkedProduct = $this->productRepository->get(
                $productLink->getLinkedProductSku(),
                false,
                $this->locator->getStore()->getId()
            );
            $data[$productId]['links']['fbt'][] = $this->processData($linkedProduct, $productLink);
        }
        $priceModifier = $this->getPriceModifier();
        /**
         * Set field name for modifier
         */
        $priceModifier->setData('name', 'price');
        if (!empty($data[$productId]['links']['fbt'])) {
            $dataMap = $priceModifier->prepareDataSource([
                'data' => [
                    'items' => $data[$productId]['links']['fbt']
                ]
            ]);
            $data[$productId]['links']['fbt'] = $dataMap['data']['items'];
        }

        return $data;
    }

    /**
     * Prepares config for the Frequently Bought Together products fieldset
     *
     * @return array
     */
    protected function getFbtFieldset()
    {
        $content = __(
            'Frequently Bought Together products are shown to customers in addition to the item the customer is looking at.'
        );

        return [
            'children' => [
                'button_set' => $this->getButtonSet(
                    $content,
                    __('Add Frequently Bought Together Products'),
                    $this->scopePrefix . static::DATA_SCOPE_FBT
                ),
                'modal' => $this->getGenericModal(
                    __('Add Frequently Bought Together Products'),
                    $this->scopePrefix . static::DATA_SCOPE_FBT
                ),
                static::DATA_SCOPE_FBT => $this->getGrid($this->scopePrefix . static::DATA_SCOPE_FBT),
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__fieldset-section',
                        'label' => __('Frequently Bought Together Products'),
                        'collapsible' => false,
                        'componentType' => Fieldset::NAME,
                        'dataScope' => '',
                        'sortOrder' => 100,
                    ],
                ],
            ]
        ];
    }

    /**
     * Retrieve all data scopes
     *
     * @return array
     */
    protected function getDataScopes()
    {
        return [
            static::DATA_SCOPE_RELATED,
            static::DATA_SCOPE_CROSSSELL,
            static::DATA_SCOPE_UPSELL,
            static::DATA_SCOPE_FBT
        ];
    }

    /**
     * Get price modifier
     *
     * @return Price
     */
    protected function getPriceModifier()
    {
        if (!$this->priceComponent) {
            $this->priceComponent = ObjectManager::getInstance()->get(
                Price::class
            );
        }

        return $this->priceComponent;
    }

    /**
     * @param ProductInterface $linkedProduct
     * @param ProductLinkInterface $linkItem
     *
     * @return array
     * @throws NoSuchEntityException
     */
    protected function processData(ProductInterface $linkedProduct, ProductLinkInterface $linkItem)
    {
        return [
            'id' => $linkedProduct->getId(),
            'thumbnail' => $this->imageHelper->init($linkedProduct, 'product_listing_thumbnail')->getUrl(),
            'name' => $linkedProduct->getName(),
            'status' => $this->status->getOptionText($linkedProduct->getStatus()),
            'attribute_set' => $this->attributeSetRepository
                ->get($linkedProduct->getAttributeSetId())
                ->getAttributeSetName(),
            'sku' => $linkItem->getLinkedProductSku(),
            'price' => $linkedProduct->getPrice(),
            'position' => $linkItem->getPosition(),
        ];
    }
}
