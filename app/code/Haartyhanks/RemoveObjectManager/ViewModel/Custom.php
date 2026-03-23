<?php
namespace Haartyhanks\RemoveObjectManager\ViewModel;

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
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\CatalogInventory\Helper\Stock;
class Custom implements ArgumentInterface
{
    protected $visibility;
    protected $date;
    protected $productCollectionFactory;
    protected $productRepository;
    protected $storeManager;
    protected $stockRegistry;
    protected $cart;
    protected $urlInterface;
    protected $session;
    protected $categoryFactory;
    protected $postFactory;
    protected $imageHelper;
    protected $customerSession;
    protected $listProduct;
    protected $registry;
    protected $viewedBlock;
    protected CheckoutSession $checkoutSession;
    protected Resolver $layerResolver;
    protected Stock $stockHelper;
    public function __construct(
        CollectionFactory $productCollectionFactory,
        ProductRepository $productRepository,
        StoreManagerInterface $storeManager,
        StockRegistryInterface $stockRegistry,
        UrlInterface $urlInterface,
        CheckoutSession $checkoutSession,
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
          Resolver $layerResolver,
        Stock $stockHelper,

    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->stockRegistry = $stockRegistry;
        $this->urlInterface = $urlInterface;
        $this->cart = $cart;
        $this->session = $session;
        $this->imageHelper = $imageHelper;
        $this->customerSession = $customerSession;
        $this->listProduct = $listProduct;
        $this->registry = $registry;
        $this->viewedBlock = $viewedBlock;
        $this->visibility = $visibility;
        $this->date = $date;
        $this->categoryFactory = $categoryFactory;
        $this->postFactory = $postFactory;
        $this->checkoutSession = $checkoutSession;
         $this->layerResolver = $layerResolver;
        $this->stockHelper = $stockHelper;
    }

    public function getProductCollection()
    {
        return $this->productCollectionFactory;
    }

    public function getCurrentProduct()
    {
        return $this->productRepository;
    }
    public function getCurrentProductData(): ?Product
    {
        return $this->registry->registry('current_product');
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
        $this->viewedBlock->setLayout(null); // Layout will be set by template
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

     public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }
    public function getCurrentCategoryProductCollection()
    {
        $collection = $this->layerResolver->get()->getProductCollection();

        if (!$collection) {
            return null;
        }

       /* $collection->addAttributeToFilter(
            'status',
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        );*/

       // $this->stockHelper->addInStockFilterToCollection($collection);

        return $collection;
    }

    public function getEnabledInStockProductCount(): int
    {
        $collection = $this->getCurrentCategoryProductCollection();
        if (!$collection) {
            return 0;
        }

        return (int) $collection->getSize();
    }

}
