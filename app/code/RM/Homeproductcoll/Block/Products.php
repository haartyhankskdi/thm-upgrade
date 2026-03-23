<?php

namespace RM\Homeproductcoll\Block;
class Products extends \Magento\Framework\View\Element\Template
{      
     protected $categoryFactory;
     protected $ProductRepositoryInterface;
     protected $storeManager;
 
     public function __construct(
     \Magento\Backend\Block\Template\Context $context,
     \Magento\Catalog\Model\CategoryFactory $CategoryFactory,
     \Magento\Catalog\Api\ProductRepositoryInterface $ProductRepositoryInterface,
     \Magento\Store\Model\StoreManagerInterface $storeManager,
     \Magento\Checkout\Helper\Cart $CartHelper,
     array $data = []
     )
     {

     $this->categoryFactory = $CategoryFactory;
     $this->productRepositoryInterface = $ProductRepositoryInterface;
     $this->storeManager = $storeManager;
     $this->cartHelper = $CartHelper;
     parent::__construct($context, $data);

     }

     public function getMediaUrl(){

       return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA). 'catalog/product';
     }
     
     public function getProductCollectionFromCategory($categoryId) {
      $category = $this->categoryFactory->create()->load($categoryId);
      $productCollection = $category->getProductCollection()
        ->addAttributeToSelect('*')
        ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
        ->setOrder('created_at', 'desc');

    return $productCollection;

     }

     public function getAddToCartUrlForProduct($productSku) {

        $product = $this->productRepositoryInterface->get($productSku);
        return $this->cartHelper->getAddUrl($product);
    }
}