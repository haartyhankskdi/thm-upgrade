<?php
namespace Haartyhanks\AutoUpsell\Block\Product\View;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product;

class AccordionAttributes extends Template
{
    protected $registry;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Get the current product from registry
     */
     public function getProduct(): ?Product
    {
        return $this->registry->registry('product');
    }

    /**
     * Get product attribute value by code
     *
     * @param string $attributeCode
     * @return string|null
     */
    public function getProductAttributeValue(string $attributeCode): ?string
    {
        $product = $this->getProduct();

        if ($product && $product->getData($attributeCode)) {
            return html_entity_decode($product->getData($attributeCode));
        }

        return null;
    }
}
