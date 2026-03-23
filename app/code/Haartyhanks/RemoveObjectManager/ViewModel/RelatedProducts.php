<?php
namespace Haartyhanks\RemoveObjectManager\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Catalog\Helper\Output;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Cms\Model\Page;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Helper\Image as ImageHelper;

class RelatedProducts implements ArgumentInterface
{
    protected $getProductSalableQty;
    protected $helper;
    protected $categoryRepository;
    protected $_productRepository;
    protected $_coreRegistry;
    protected $listBlock;
    protected ImageHelper $imageHelper;
    protected $cart;
    protected $customerSession;
    protected $cmsPage;
    protected $urlBuilder;
    protected $layerResolver;

    public function __construct(
        GetProductSalableQtyInterface $getProductSalableQty,
        ProductRepositoryInterface $productRepository,
        Output $helper,
        ImageHelper $imageHelper,
        ListProduct $listBlock,
        Registry $coreRegistry,
        Cart $cart,
        Page $cmsPage,
        UrlInterface $urlBuilder,
        Session $customerSession,
        CategoryRepositoryInterface $categoryRepository,
        LayerResolver $layerResolver
    ) {
        $this->getProductSalableQty = $getProductSalableQty;
        $this->helper = $helper;
        $this->_productRepository = $productRepository;
        $this->_coreRegistry = $coreRegistry;
        $this->listBlock = $listBlock;
        $this->imageHelper = $imageHelper;
        $this->cart = $cart;
        $this->cmsPage = $cmsPage;
        $this->customerSession = $customerSession;
        $this->urlBuilder = $urlBuilder;
        $this->categoryRepository = $categoryRepository;
        $this->layerResolver = $layerResolver;
    }

    public function getSalableQuantity($sku)
    {
        return $this->getProductSalableQty->execute($sku, 1);
    }

    public function getCurrentCategory()
    {
        $layer = $this->layerResolver->get();
        return $layer ? $layer->getCurrentCategory() : null;
    }

    public function getCategoryById($categoryId)
    {
        try {
            return $this->categoryRepository->get($categoryId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    
    public function getCurrentProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }


    public function getProductCollectionFromCategory($categoryId)
    {
        $category = $this->getCategoryById($categoryId);
        if ($category) {
            return $category->getProductCollection()
                ->addAttributeToSelect(['name','price','small_image','special_price','special_from_date','special_to_date','weight'])
                ->addAttributeToFilter('status', 1)
                ->setPageSize(12);
        }
        return [];
    }

    
    public function getExactCategoryProducts()
    {
        $currentCategory = $this->getCurrentCategory();
        $currentProduct = $this->getCurrentProduct();
        if ($currentCategory && $currentProduct && in_array($currentCategory->getId(), $currentProduct->getCategoryIds())) {
            return $this->getProductCollectionFromCategory($currentCategory->getId());
        }
        if ($currentProduct) {
            $categoryIds = $currentProduct->getCategoryIds();

            if (!empty($categoryIds)) {
                $childCategoryId = null;
                $parentCategoryId = null;

                foreach ($categoryIds as $catId) {
                    $category = $this->getCategoryById($catId);
                    if ($category) {
                       
                        if ($category->getParentId() > 2) {
                            $childCategoryId = $catId;
                            break;
                        } else {
                            $parentCategoryId = $catId;
                        }
                    }
                }

                if ($childCategoryId) {
                    return $this->getProductCollectionFromCategory($childCategoryId);
                } elseif ($parentCategoryId) {
                    return $this->getProductCollectionFromCategory($parentCategoryId);
                }
            }
        }

        return [];
    }

   
    public function getTotalItems()
    {
        return $this->cart->getQuote()->getItemsCount();
    }

    public function getTotalQuantity()
    {
        return $this->cart->getQuote()->getItemsQty();
    }

    public function getCartSubtotal()
    {
        return number_format((float)$this->cart->getQuote()->getSubtotal(), 2, '.', '');
    }

    public function getCmsPageIdentifier()
    {
        return $this->cmsPage->getIdentifier();
    }

    public function getCurrentUrl()
    {
        return $this->urlBuilder->getCurrentUrl();
    }
    public function getImageHelper()
    {
        return $this->imageHelper;
    }

}
