<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Hyva\Widgets\Block\WidgetConfigs;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class CategoryList implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $categoryCollection;
    
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * CategoryList constructor.
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $categoryCollection
     * @param array $data
     */

    public function __construct(
        StoreManagerInterface $storeManager,
        CollectionFactory $categoryCollection,
        $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->categoryCollection = $categoryCollection;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function toOptionArray(): array
    {
        return $this->getCategories();
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCategories(): array
    {
        $categories = $this->categoryCollection->create()
            ->addAttributeToSelect('*')
            ->setStoreId($this->storeManager->getStore()); //categories from current store will be fetched

        $arr = [];

        foreach ($categories as $category) {

            $arr[] = ['value' => $category->getId(), 'label' => __($category->getId() . ' - ' . $category->getName())];

        }
        return $arr;
    }
}
