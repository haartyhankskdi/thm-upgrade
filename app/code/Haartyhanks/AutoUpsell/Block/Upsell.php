<?php
namespace Haartyhanks\AutoUpsell\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Customer\Model\Session;

class Upsell extends Template
{
    protected $registry;
    protected $collectionFactory;
    protected $categoryFactory;
    protected $formKey;
    protected $customerSession;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        CollectionFactory $collectionFactory,
        CategoryFactory $categoryFactory,
        FormKey $formKey,
        Session $customerSession,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->collectionFactory = $collectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->formKey = $formKey;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

  public function getAutoUpsellProducts($limit = 15)
    {
        $product = $this->registry->registry('current_product');
        if (!$product || !$product->getId()) return [];

        $categoryIds = $product->getCategoryIds();

        if (empty($categoryIds)) return [];

        // Print all category IDs
        //echo '<pre>Category IDs: ';
        //print_r($categoryIds);
       // echo '</pre>';

        $defaultCategoryId = $categoryIds[0];

        // Also print default category ID
        //echo '<pre>Default Category ID: ' . $defaultCategoryId . '</pre>';

        $category = $this->categoryFactory->create()->load($defaultCategoryId);
        if (!$category || !$category->getId()) return [];

        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addCategoriesFilter(['in' => [$defaultCategoryId]])
            ->addFieldToFilter('entity_id', ['neq' => $product->getId()])
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('visibility', ['neq' => 1])
            ->setPageSize($limit);

        return $collection;
    }


    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getWishlistHelper()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Wishlist\Helper\Data::class);
    }

   
}
