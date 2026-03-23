<?php
namespace Haartyhanks\RemoveObjectManager\Block;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\UrlInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Catalog\Helper\Image;
use Magento\Customer\Model\Session;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\Registry;
use Magento\Reports\Block\Product\Viewed as ViewedBlock;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magefan\Blog\Model\CategoryFactory;
use Mageplaza\Blog\Model\PostFactory;

class RemoveObj extends \Magento\Framework\View\Element\Template
{
     protected $visibility;
     protected $date;
    /**
     * @var CollectionFactory
    */
    protected $productCollectionFactory;
    /**
     * @var ProductRepository
    */
    protected $productRepository;
    /**
     * @var StoreManagerInterface
    */
    protected $storeManager;
    /**
     * @var StockRegistryInterface
    */
    protected $stockRegistry;
    /**
     * @var Cart
    */
    protected $cart;
    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var SessionManagerInterface
     */
    protected $session;
    protected $categoryFactory;
    protected $postFactory;
    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ListProduct
     */
    protected $listProduct;

    /**
     * @var Registry
     */
    protected $registry;
    protected $viewedBlock;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CollectionFactory $productCollectionFactory,
        ProductRepository $productRepository,
        StoreManagerInterface $storeManager,
        StockRegistryInterface $stockRegistry,
        UrlInterface $urlInterface,
        Cart $cart,
        SessionManagerInterface $session,
        Image $imageHelper,
        ViewedBlock $viewedBlock,
        Session $customerSession,
        ListProduct $listProduct,
        Registry $registry,
        Visibility $visibility,
        DateTime $date,
        CategoryFactory $categoryFactory,
         PostFactory $postFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productCollectionFactory         = $productCollectionFactory;
        $this->productRepository                = $productRepository;
        $this->storeManager                     = $storeManager;
        $this->stockRegistry                    = $stockRegistry;
        $this->urlInterface                     = $urlInterface;
        $this->cart                             = $cart;
        $this->session                          = $session;
        $this->imageHelper                      = $imageHelper;
        $this->customerSession                  = $customerSession;
        $this->listProduct                      = $listProduct;
        $this->registry                         = $registry;
        $this->viewedBlock                      = $viewedBlock;
        $this->visibility                       = $visibility;
        $this->date                             = $date;
        $this->categoryFactory = $categoryFactory;
        $this->postFactory = $postFactory;
    }

    public function getProductCollection()
    {
        $collection = $this->productCollectionFactory;
        return $collection;
    }

    public function getCurrentProduct()
    {
        $currentProductId = $this->productRepository;
        return $currentProductId;
    }

    public function getProductRepository()
    {
        return $this->productRepository;
    }

    public function getStoreUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getStockRegistry()
    {
        return $this->stockRegistry;
    }

    public function getCart()
    {
        return $this->cart;
    }
    public function getCurrentPageUrl()
    {
        return $this->urlInterface->getCurrentUrl();
    }
    public function getRecentView()
    {
        $this->viewedBlock->setLayout($this->getLayout());

        $collection = $this->viewedBlock->getItemsCollection();
        $collection->addAttributeToSelect(['name', 'price', 'small_image']);
        $collection->setPageSize(4);

        return $collection;
    }
    public function getSession()
    {
        return $this->session;
    }

    public function getImageHelper()
    {
        return $this->imageHelper;
    }

    public function getCustomerData()
    {
        return $this->customerSession;
    }

    public function getListProduct()
    {
        return $this->listProduct;
    }

    public function getCurrentProductId()
    {
        return $this->registry->registry('current_product');
    }

    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }
     public function getVisibleTypes()
    {
        return $this->visibility->getVisibleInCatalogIds();
    }

    public function getCurrentDate()
    {
         return $this->date->gmtDate();
    }
     public function getCategoryCollection()
    {
        return $this->categoryFactory->create()->getCollection();
    }
    public function getCategoriesByPostId($postId)
    {
        $post = $this->postFactory->create()->load($postId);
        return $post->getSelectedCategoriesCollection();
    }

}
