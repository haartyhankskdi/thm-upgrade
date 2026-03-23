<?php
namespace Haartyhanks\RemoveObjectManager\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;

class StockManagement extends Template
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

    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }
}
