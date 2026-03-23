<?php

namespace RM\Homeproductcoll\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Helper\Cart;

class Products implements ArgumentInterface
{
    protected $categoryFactory;
    protected $productRepositoryInterface;
    protected $storeManager;
    protected $cartHelper;

    public function __construct(
        CategoryFactory $categoryFactory,
        ProductRepositoryInterface $productRepositoryInterface,
        StoreManagerInterface $storeManager,
        Cart $cartHelper
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->storeManager = $storeManager;
        $this->cartHelper = $cartHelper;
    }

    public function getMediaUrl()
    {
        return $this->storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            . 'catalog/product';
    }

    public function getProductCollectionFromCategory($categoryId)
    {
        $category = $this->categoryFactory->create()->load($categoryId);
        $productCollection = $category->getProductCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter(
                'status',
                \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
            )
            ->setOrder('created_at', 'desc');

        return $productCollection;
    }

    public function getAddToCartUrlForProduct($productSku)
    {
        $product = $this->productRepositoryInterface->get($productSku);
        return $this->cartHelper->getAddUrl($product);
    }
}
