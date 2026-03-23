<?php
/**
 * Copyright © RM Dubey All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace RM\BetterSearch\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use RM\BetterSearch\Model\Indexer\Search;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\CategoryFactory as CategoryCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{

    /**
     * @var StoreManagerInterface
     */
     protected $storeManager;
    
     /**
     * @var ScopeConfigInterface
     */
     protected $_scopeConfig;
   
     /**
     * @var categoryCollectionFactory
     */
     protected $_categoryCollectionFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StoreManagerInterface $storeManagerInterface,
        ScopeConfigInterface $scopeConfig,
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManagerInterface;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function foldername()
    {
        return Search::XML_FOLDER_NAME;
    }

    public function storeCode()
    {
        return $this->storeManager->getStore()->getCode();
    }

    public function mediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getPlaceholderImage(){
        /* $this->_scopeConfig->getValue('catalog/placeholder/image_placeholder'); // Base Image
        $this->_scopeConfig->getValue('catalog/placeholder/small_image_placeholder'); // Small Image
        $this->_scopeConfig->getValue('catalog/placeholder/swatch_image_placeholder'); // Swatch Image */
        return $this->_scopeConfig->getValue('catalog/placeholder/thumbnail_placeholder'); // Thumbnail Image
    }

    public function getSearchOptionCategory()
    {
        // 
        $optionArray = array();
        $rootCatId = $this->getRootCatId();
        $categories = $this->getCategory($rootCatId);
        // Level one
        $child_cat_list = "";
        $sub_cat = explode(",", $categories->getChildren()); 
        // exit($categories->getChildren());
        if ($categories->hasChildren()) {
            for ($i = 0; $i < count($sub_cat); $i++) {
                $sub = $this->getCategory($sub_cat[$i]);
                if ($sub->getIsActive()) {
                    $optionArray[] = array(
                        'name' => $sub->getName(),
                        'value' => $sub->getId(),
                        // 'data-value' => $child_cat_list
                    );
                }
            }
        }

        return $optionArray;
    }

    private function getLoopChildCategory($catId, $child)
    { 
        $categories = $this->getCategory($catId);
        // Level one
        $child_cat_list = $child.',';
        $sub_cat = explode(",", $categories->getChildren());
        if ($categories->hasChildren()) {
            for ($i = 0; $i < count($sub_cat); $i++) {
                $child_cat_list .= $this->getLoopChildCategory($sub_cat[$i], $child_cat_list);
            }
        }
        return $child_cat_list;
    }

    private function getCategory($id)
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->load($id);
        return $collection;
    }

    private function getRootCatId()
    {
        # get core_config data
        // $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        // return $this->scopeConfig->getValue(self::GET_PARENT_CATEGORY, $storeScope);
        return $this->storeManager->getStore()->getRootCategoryId();
    }

}