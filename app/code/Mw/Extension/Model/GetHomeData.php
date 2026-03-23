<?php

namespace Mw\Extension\Model;

use Mw\Extension\Api\GetHomeInterface;

class GetHomeData implements GetHomeInterface
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

    protected $searchCriteriaBuilder;
    public function __construct(
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->appEmulation = $appEmulation;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }
    public function getHomeProducts()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
        $products  = [];
        $collection = [];
        $this->searchCriteriaBuilder->addFilter(
            'app_homepage_product',
            '1',
            'eq'
        )->addFilter('status', '1', 'eq');

        $collection = $this->productRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        foreach ($collection as $p) {
            $single_product = $p->getData();
            // if ($single_product->appHomepageProduct() === 1) {
            $product_Image = $this->productRepository->get($p->getSku());
            if (!$product_Image) {
                $single_product['image_url'] = '';
            } else {
                $image_url = $this->imageHelper->init($product_Image, 'product_base_image')->getUrl();
                $single_product['image_url'] = $image_url;
            }
            array_push($products, $single_product);
        }
        // }
        $response = [
            'status' => 1,
            'message' => 'success',
            "data" => $products
        ];

        return json_encode($response);
        $this->appEmulation->stopEnvironmentEmulation();
        exit();
    }
}
