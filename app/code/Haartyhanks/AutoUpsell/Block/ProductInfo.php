<?php
namespace Haartyhanks\AutoUpsell\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Registry;


class ProductInfo extends Template
{
    protected $stockRegistry;
     protected $registry;


    public function __construct(
        Template\Context $context,
        StockRegistryInterface $stockRegistry,
        Registry $registry,
        array $data = []
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

     public function isInStock(Product $product): bool
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId());
        return $stockItem->getIsInStock();
    }

    public function getProduct(): ?Product
    {
        return $this->registry->registry('current_product');
    }
}
