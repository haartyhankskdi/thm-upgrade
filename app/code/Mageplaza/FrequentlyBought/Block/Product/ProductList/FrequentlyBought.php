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

namespace Mageplaza\FrequentlyBought\Block\Product\ProductList;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ProductList\Related;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Checkout\Model\ResourceModel\Cart;
use Magento\Checkout\Model\Session;
use Magento\Downloadable\Block\Catalog\Product\Links;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\FormKey;
use Magento\GroupedProduct\Block\Product\View\Type\Grouped;
use Mageplaza\FrequentlyBought\Block\Product\View\Type\Configurable;
use Mageplaza\FrequentlyBought\Helper\Data as FbtData;
use Mageplaza\FrequentlyBought\Model\Config\Source\Method;
use Mageplaza\FrequentlyBought\Model\FrequentlyBought as FrequentlyBoughtModel;
use Mageplaza\FrequentlyBought\Model\FrequentlyBoughtFactory;
use Mageplaza\FrequentlyBought\Model\ResourceModel\FrequentlyBought\Product\Collection as FrequentlyBoughtCollection;
use Zend_Db_Expr;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class FrequentlyBought
 *
 * @package Mageplaza\FrequentlyBought\Block\Product\ProductList
 */
class FrequentlyBought extends Related
{
    /**
     * @var Data
     */
    protected $priceHelper;

    /**
     * @var FbtData
     */
    protected $fbtDataHelper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var FrequentlyBoughtModel
     */
    protected $fbtModelFactory;

    /**
     * @var Collection
     */
    protected $_productCollection;

    /**
     * @type string
     */
    protected $formKey;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrencyInterface;

    /**
     * FrequentlyBought constructor.
     *
     * @param Context $context
     * @param Cart $checkoutCart
     * @param Visibility $catalogProductVisibility
     * @param Session $checkoutSession
     * @param Manager $moduleManager
     * @param Data $priceHelper
     * @param ProductRepositoryInterface $productRepository
     * @param FbtData $fbtDataHelper
     * @param FormatInterface $localeFormat
     * @param CollectionFactory $productCollectionFactory
     * @param FrequentlyBoughtFactory $fbtModelFactory
     * @param PriceCurrencyInterface $priceCurrencyInterface
     * @param array $data
     */
    public function __construct(
        Context                          $context,
        Cart                             $checkoutCart,
        Visibility                       $catalogProductVisibility,
        Session                          $checkoutSession,
        Manager                          $moduleManager,
        Data                             $priceHelper,
        ProductRepositoryInterface       $productRepository,
        FbtData                          $fbtDataHelper,
        FormatInterface                  $localeFormat,
        CollectionFactory                $productCollectionFactory,
        FrequentlyBoughtFactory          $fbtModelFactory,
        PriceCurrencyInterface           $priceCurrencyInterface,
        array                            $data = []
    ) {
        $this->priceHelper = $priceHelper;
        $this->productRepository = $productRepository;
        $this->fbtDataHelper = $fbtDataHelper;
        $this->_localeFormat = $localeFormat;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->fbtModelFactory = $fbtModelFactory;
        $this->priceCurrencyInterface = $priceCurrencyInterface;

        parent::__construct(
            $context,
            $checkoutCart,
            $catalogProductVisibility,
            $checkoutSession,
            $moduleManager,
            $data
        );

        if ($this->fbtDataHelper->getDisplayStyle() === 'slider') {
            if($this->fbtDataHelper->checkHyvaTheme()) {
                $this->setTemplate('Mageplaza_FrequentlyBought::hyva/product/slider/items.phtml');
            } else {
                $this->setTemplate('Mageplaza_FrequentlyBought::product/slider/items.phtml');
            }
        } else {
            if($this->fbtDataHelper->checkHyvaTheme()) {
                $this->setTemplate('Mageplaza_FrequentlyBought::hyva/product/list/items.phtml');
            } else {
                $this->setTemplate('Mageplaza_FrequentlyBought::product/list/items.phtml');

            }
        }
    }

    /**
     * @param $product
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getProductIsSalable($product)
    {
        return $product->isSalable();
    }

    /**
     * @return bool
     */
    public function isShow()
    {
        return !(!$this->fbtDataHelper->isEnabled() || $this->getRequest()->isAjax());
    }

    /**
     * @return Collection|FrequentlyBoughtCollection
     * @throws LocalizedException
     */
    public function getItems()
    {
        if (!in_array(Method::FBT, $this->fbtDataHelper->getProductMethod(), true)) {
            return parent::getItems();
        }

        if (!in_array(Method::RELATED, $this->fbtDataHelper->getProductMethod(), true)) {
            return $this->getFbtProducts();
        }

        return $this->getProductCollection();
    }

    /**
     * @return Collection|FrequentlyBoughtCollection
     */
    protected function getFbtProducts()
    {
        if ($this->_productCollection === null) {
            /** @var FrequentlyBoughtModel $model */
            $model = $this->fbtModelFactory->create();
            $this->_productCollection = $model->getProductCollection($this->getProduct());
            $this->_productCollection->addAttributeToSelect('required_options')->setPositionOrder()->addStoreFilter();
            if ($this->moduleManager->isEnabled('Magento_Checkout')) {
                $this->_addProductAttributesAndPrices($this->_productCollection);
            }
            $this->_productCollection->setVisibility($this->_catalogProductVisibility->getVisibleInSiteIds());

            $this->_productCollection->load();

            foreach ($this->_productCollection as $product) {
                $product->setDoNotUseCategoryId(true);
            }
        }

        return $this->_productCollection;
    }

    /**
     * @return Collection|FrequentlyBoughtCollection
     * @throws LocalizedException
     */
    protected function getProductCollection()
    {
        $product = $this->getProduct();
        $productId = $product->getId();
        if (!$this->fbtDataHelper->hasProductLinks($productId)) {
            return parent::getItems();
        }
        if ($this->_productCollection === null) {
            $fbtProducts = $this->getFbtProducts();
            $limit = (int)$this->fbtDataHelper->getConfigGeneral('item_limit');
            $size = $fbtProducts->getSize();
            if ($limit && $size >= $limit) {
                return $fbtProducts;
            }
            $productIds = $fbtProducts->getAllIds();
            $relatedProducts = $product->getRelatedProductCollection()->addAttributeToSelect(
                'required_options'
            )->setPositionOrder()->addStoreFilter();
            foreach ($relatedProducts as $item) {
                if ($limit && $size >= $limit) {
                    break;
                }
                if (!in_array($item->getId(), $productIds, true)) {
                    $productIds[] = $item->getId();
                    $size++;
                }
            }

            $this->_productCollection = $this->productCollectionFactory->create();
            $this->_productCollection->getSelect()
                ->where('e.entity_id IN (?)', $productIds);
            if (!empty($productIds)) {
                $this->_productCollection->getSelect()
                    ->order(new Zend_Db_Expr('FIELD(e.entity_id,' . implode(',', $productIds) . ')'));
            }
            $this->_productCollection->addAttributeToSelect(
                'required_options'
            );
            if ($this->moduleManager->isEnabled('Magento_Checkout')) {
                $this->_addProductAttributesAndPrices($this->_productCollection);
            }
            $this->_productCollection->setVisibility($this->_catalogProductVisibility->getVisibleInSiteIds());
            $this->_productCollection->load();

            foreach ($this->_productCollection as $product) {
                $product->setDoNotUseCategoryId(true);
            }
        }

        return $this->_productCollection;
    }

    /**
     * @inheritdoc
     */
    protected function _addProductAttributesAndPrices(Collection $collection)
    {
        $collection = parent::_addProductAttributesAndPrices($collection);

        $itemLimit = (int)$this->fbtDataHelper->getConfigGeneral('item_limit');
        if ($itemLimit) {
            $collection->getSelect()
                ->limit($itemLimit);
        }

        return $collection;
    }

    /**
     * @return string
     */
    public function getJsonConfig()
    {
        $config = [
            'priceFormat' => $this->_localeFormat->getPriceFormat(),
            'usePopup' => $this->usePopup(),
            'useSlider' => $this->useSlider()
        ];

        return FbtData::jsonEncode($config);
    }

    /**
     * Get heading label
     *
     * @return string
     */
    public function getTitleBlock()
    {
        return $this->fbtDataHelper->getConfigGeneral('block_name');
    }

    /**
     * Get price with currency
     *
     * @param float $price
     *
     * @return string
     */
    public function getPriceWithCurrency($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }

    /**
     * Format price with currency
     *
     * @param float $price
     *
     * @return string
     */
    public function formatPriceWithCurrency($price)
    {
        return $this->priceCurrencyInterface->format($price, false);
    }

    /**
     * Get price without currency
     *
     * @param object $product
     *
     * @return float
     */
    public function getPriceAmount($product)
    {
        $productType = $product->getTypeId();
        if ($productType === 'grouped' || $productType === 'bundle') {
            return $product->getMinimalPrice();
        }

        return $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
    }

    /**
     * Get all custom option product
     *
     * @param null $productId
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getCustomOption($productId = null)
    {
        $product = $this->getProductById($productId);
        $option = $this->getLayout()->getBlock('mageplaza.frequently.bought.product.info.options')
            ->setProduct($product)
            ->toHtml();
        return $option;
    }

    /**
     * Get option product
     *
     * @param null $productId
     *
     * @return bool|BlockInterface|string
     * @throws LocalizedException
     */
    public function getOptionWrapper($productId = null)
    {
        $html = '';
        $product = $this->getProductById($productId);
        $productType = $product->getTypeId();
        switch ($productType) {
            case 'configurable':
                $html = $this->getLayout()->createBlock(Configurable::class);
                break;
            case 'grouped':
                if($this->fbtDataHelper->checkHyvaTheme()) {
                    $html = $this->getLayout()->createBlock(Grouped::class)
                        ->setTemplate('Mageplaza_FrequentlyBought::hyva/product/view/type/grouped.phtml');
                } else {
                    $html = $this->getLayout()->createBlock(Grouped::class)
                        ->setTemplate('Mageplaza_FrequentlyBought::product/view/type/grouped.phtml');
                }

                break;
            case 'bundle':
                $html = $this->getLayout()->getBlock('mageplaza.fbt.product.info.bundle.options');
                break;
            case 'downloadable':
                $html = $this->getLayout()->createBlock(Links::class)
                    ->setTemplate('Mageplaza_FrequentlyBought::product/view/type/downloadable/links.phtml');
                break;
        }
        if ($html) {
            return $html->setProduct($product)->toHtml();
        }

        return $html;
    }

    /**
     * Get product by id
     *
     * @param null $productId
     *
     * @return ProductInterface|Product
     * @throws NoSuchEntityException
     */
    protected function getProductById($productId = null)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        if ($productId) {
            $product = $this->productRepository->getById($productId, false, $storeId);
        } else {
            $product = $this->getProduct();
        }

        return $product;
    }

    /**
     * Get separator image config
     *
     * @return string
     */
    public function getSeparatorImage()
    {
        return $this->fbtDataHelper->getIcon();
    }

    /**
     * Get add to wishlist config
     *
     * @return mixed
     */
    public function getShowWishList()
    {
        return $this->fbtDataHelper->getConfigGeneral('enable_add_to_wishlist');
    }

    /**
     * Use Popup to select product options
     * @return bool
     */
    public function usePopup()
    {
        return $this->fbtDataHelper->usePopup();
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getFormKeyHtml()
    {
        if (!$this->formKey) {
            $this->formKey = $this->getLayout()->createBlock(FormKey::class)->toHtml();
        }

        return $this->formKey;
    }

    /**
     * @return bool
     */
    public function useSlider()
    {
        return $this->fbtDataHelper->getDisplayStyle() === 'slider';
    }

    /**
     * @return int
     */
    public function getNumberOfProductOnSlider()
    {
        $pageLayout = $this->getProductLayoutDesign();
        $number = $this->fbtDataHelper->getNumberOfProductOnSlider();
        switch ($pageLayout) {
            case '2columns-left':
            case '2columns-right':
            case 'category-full-width':
                $number = $number >= 2 || empty($number) ? 2 : $number;
                break;
            case '3columns':
                $number = 1;
                break;
            default:
                $number = $number >= 3 || empty($number) ? 3 : $number;
                break;
        }

        return $number;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getProductLayoutDesign()
    {
        return $this->getProductById()->getPageLayout();
    }

    /**
     * @return mixed
     */
    public function getHelperData()
    {
        return $this->fbtDataHelper;
    }
}
