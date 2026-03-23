<?php
namespace Haartyhanks\RemoveObjectManager\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Form\FormKey;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory as BestsellersCollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;

class BestSellers extends Template
{
    /**
     * @var BestsellersCollectionFactory
     */
      private $bestsellersFactory;
      protected $imageBuilder;
      protected $formKey;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepo;

    public function __construct(
        Template\Context $context,
        FormKey $formKey,
        BestsellersCollectionFactory $bestsellersFactory,
        ProductRepositoryInterface $productRepo,
        ImageBuilder $imageBuilder,
        array $data = []

    ) {
        parent::__construct($context, $data);
        $this->formKey = $formKey;
        $this->imageBuilder = $imageBuilder;
        $this->bestsellersFactory = $bestsellersFactory;
        $this->productRepo       = $productRepo;
    }

    /**
     * Retrieve best‑selling products
     *
     * @param int $limit
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getBestSellingProducts($limit = 1)
    {
        $storeId = $this->_storeManager->getStore()->getId();
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
    public function getProductImage(Product $product)
    {
        return $this->imageBuilder
            ->setProduct($product)
            ->setImageId('category_page_grid')
            ->create();
    }
     public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
