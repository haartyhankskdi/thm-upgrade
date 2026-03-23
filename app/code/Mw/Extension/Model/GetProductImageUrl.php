<?php

namespace Mw\Extension\Model;

use Mw\Extension\Api\GetProductImageInterface;

class GetProductImageUrl implements GetProductImageInterface
{
    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $appEmulation;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;
    /**
     * @param \Magento\Store\Model\App\Emulation              $appEmulation
     * @param \Magento\Store\Model\StoreManagerInterface      $storeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Helper\Image                   $imageHelper
     */
    public function __construct(
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Image $imageHelper
    ) {
        $this->appEmulation = $appEmulation;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
    }
    public function getProductImageUrl($sku)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $product = $this->productRepository->get($sku);
        $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
        if (!$product) {
            $data_result = array('status' => 0, 'message' => 'Incorrect SKU code!', 'data' => null);
            return json_encode($data_result);
        } else {
            $image_url = $this->imageHelper->init($product, 'product_base_image')->getUrl();
            $data_result = array('status' => 1, 'message' => 'Success', 'data' => $image_url);
            return json_encode($data_result);
        }
        $this->appEmulation->stopEnvironmentEmulation();
    }
}
