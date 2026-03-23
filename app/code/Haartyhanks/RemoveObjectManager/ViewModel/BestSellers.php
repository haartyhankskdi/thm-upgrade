<?php
namespace Haartyhanks\RemoveObjectManager\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Form\FormKey;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory as BestsellersCollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class BestSellers implements ArgumentInterface
{
    /**
     * @var BestsellersCollectionFactory
     */
    private $bestsellersFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepo;

    /**
     * @var ImageBuilder
     */
    private $imageBuilder;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        FormKey $formKey,
        BestsellersCollectionFactory $bestsellersFactory,
        ProductRepositoryInterface $productRepo,
        ImageBuilder $imageBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->formKey = $formKey;
        $this->imageBuilder = $imageBuilder;
        $this->bestsellersFactory = $bestsellersFactory;
        $this->productRepo = $productRepo;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve best-selling products
     *
     * @param int $limit
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getBestSellingProducts($limit = 1)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $collection = $this->bestsellersFactory->create()
            ->setModel(\Magento\Catalog\Model\Product::class)
            ->addStoreFilter($storeId)
            ->setPageSize($limit)
            ->setOrder('qty_ordered', 'DESC');

        $products = [];
        foreach ($collection as $row) {
            try {
                $products[] = $this->productRepo->getById($row->getProductId(), false, $storeId);
            } catch (\Exception $e) {
                
            }
        }

        return $products;
    }

    /**
     * Build product image
     */
    public function getProductImage(Product $product)
    {
        return $this->imageBuilder
            ->setProduct($product)
            ->setImageId('category_page_grid')
            ->create();
    }

    /**
     * Retrieve form key
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
