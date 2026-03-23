<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Helper;

use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPoints\Helper\Data as RewardHelper;

/**
 * Class Data
 * @package Mageplaza\RewardPointsPro\Helper
 */
class Data extends RewardHelper
{
    const ACTION_HOLDING_POINTS  = 'holding_points';
    const ACTION_TYPE_HOLDING    = 3;
    const CONFIG_TRANS_HOLD_PATH = 'rewardpoints/transactions/hold_points';

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $timeZone
     * @param SessionFactory $sessionFactory
     * @param DesignInterface $theme
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $timeZone,
        SessionFactory $sessionFactory,
        DesignInterface $theme,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;

        parent::__construct(
            $context,
            $objectManager,
            $storeManager,
            $priceCurrency,
            $timeZone,
            $sessionFactory,
            $theme
        );
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param SearchResultsInterface $searchResult
     *
     * @return mixed
     */
    public function processGetList($searchCriteria, $searchResult)
    {
        if (!$searchCriteria) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        }

        if ($this->versionCompare('2.2.0')) {
            $collectionProcessor = $this->objectManager->get(CollectionProcessorInterface::class);
            $joinProcessor       = $this->objectManager->get(JoinProcessorInterface::class);

            $collectionProcessor->process($searchCriteria, $searchResult);
            $joinProcessor->process($searchResult);
        } else {
            /**
             * Support for customer using reward points on magento < 2.2.0
             */
            foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
                $this->addFilterGroupToCollection($filterGroup, $searchResult);
            }

            $sortOrders = $searchCriteria->getSortOrders();
            if ($sortOrders === null) {
                $sortOrders = [];
            }
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $field = $sortOrder->getField();
                $searchResult->addOrder(
                    $field,
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }

            $searchResult->setCurPage($searchCriteria->getCurrentPage());
            $searchResult->setPageSize($searchCriteria->getPageSize());
        }

        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }

    /**
     * @param FilterGroup $filterGroup
     * @param SearchResultsInterface $searchResult
     */
    protected function addFilterGroupToCollection($filterGroup, $searchResult)
    {
        $fields     = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition    = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $conditions[] = [$condition => $filter->getValue()];
            $fields[]     = $filter->getField();
        }
        if ($fields) {
            $searchResult->addFieldToFilter($fields, $conditions);
        }
    }
}
