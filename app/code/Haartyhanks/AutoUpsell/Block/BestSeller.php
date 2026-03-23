<?php

namespace Haartyhanks\AutoUpsell\Block;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory as BestSellersCollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Wishlist\Helper\Data as WishlistHelper;

class BestSeller extends Template
{
    protected $_bestSellersCollectionFactory;
    protected $_productCollectionFactory;
    protected $_storeManager;
    protected $_formKey;
    protected $_customerSession;
    protected $_wishlistHelper;

    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        BestSellersCollectionFactory $bestSellersCollectionFactory,
        FormKey $formKey,
        CustomerSession $customerSession,
        WishlistHelper $wishlistHelper,
        array $data = []   // always keep $data last for block inheritance
    ) {
        $this->_bestSellersCollectionFactory = $bestSellersCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_formKey = $formKey;
        $this->_customerSession = $customerSession;
        $this->_wishlistHelper = $wishlistHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get Bestseller Products (limit 3)
     */
    public function getProductCollection()
    {
        $productIds = [];
        $bestSellers = $this->_bestSellersCollectionFactory->create()
            ->setPeriod('month');

        foreach ($bestSellers as $product) {
            $productIds[] = $product->getProductId();
        }

        if (empty($productIds)) {
            return $this->_productCollectionFactory
                ->create()
                ->addAttributeToSelect('*')
                ->setPageSize(3);
        }

        $collection = $this->_productCollectionFactory->create()->addIdFilter($productIds);
        $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect('*')
            ->addStoreFilter($this->getStoreId())
            ->setPageSize(10); 

        return $collection;
    }

    /**
     * Get Store ID
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get Form Key (for Add to Cart, Wishlist, etc.)
     */
    public function getFormKey()
    {
        return $this->_formKey->getFormKey();
    }

    /**
     * Check if customer is logged in
     */
    public function isCustomerLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    /**
     * Get Wishlist Helper
     */
    public function getWishlistHelper()
    {
        return $this->_wishlistHelper;
    }
}
