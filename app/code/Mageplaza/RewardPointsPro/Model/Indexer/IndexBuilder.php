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

namespace Mageplaza\RewardPointsPro\Model\Indexer;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Profiler;
use Mageplaza\RewardPointsPro\Model\CatalogRule;
use Mageplaza\RewardPointsPro\Model\ResourceModel\CatalogRule\CollectionFactory as RuleCollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Class IndexBuilder
 * @package Mageplaza\RewardPointsPro\Model\Indexer
 */
class IndexBuilder
{
    const SECONDS_IN_DAY = 86400;
    const MAGEPLAZA_REWARD_CATALOGRULE_PRODUCT_TABLE = 'mageplaza_reward_catalogrule_product';

    /**
     * CatalogRuleGroupWebsite columns list
     * This array contain list of CatalogRuleGroupWebsite table columns
     * @var array
     */
    protected $_catalogRuleGroupWebsiteColumnsList = ['rule_id', 'customer_group_id', 'website_id'];

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var RuleCollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Product[]
     */
    protected $loadedProducts;

    /**
     * @var int
     */
    protected $batchCount;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * IndexBuilder constructor.
     *
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param ResourceConnection $resource
     * @param LoggerInterface $logger
     * @param ProductFactory $productFactory
     * @param int $batchCount
     */
    public function __construct(
        RuleCollectionFactory $ruleCollectionFactory,
        ResourceConnection $resource,
        LoggerInterface $logger,
        ProductFactory $productFactory,
        $batchCount = 1000
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->logger = $logger;
        $this->productFactory = $productFactory;
        $this->batchCount = $batchCount;
    }

    /**
     * @param $id
     *
     * @throws LocalizedException
     */
    public function reindexById($id)
    {
        $this->reindexByIds([$id]);
    }

    /**
     * @param array $ids
     *
     * @return void
     * @throws LocalizedException
     * @api
     */
    public function reindexByIds(array $ids)
    {
        try {
            $this->doReindexByIds($ids);
        } catch (Exception $e) {
            $this->critical($e);
            throw new LocalizedException(
                __('Catalog rule indexing failed. See details in exception log.')
            );
        }
    }

    /**
     * Reindex by ids. Template method
     *
     * @param $ids
     *
     * @throws Exception
     */
    protected function doReindexByIds($ids)
    {
        $this->cleanByIds($ids);
        foreach ($this->getActiveRules() as $rule) {
            foreach ($ids as $productId) {
                $this->applyRule($rule, $this->getProduct($productId));
            }
        }
    }

    /**
     * Full reindex
     * @return void
     * @throws LocalizedException
     * @api
     */
    public function reindexFull()
    {
        try {
            $this->doReindexFull();
        } catch (Exception $e) {
            $this->critical($e);
            throw new LocalizedException(__('Error reindex : %1', $e->getMessage()), $e);
        }
    }

    /**
     * Full reindex Template method
     * @return void
     */
    protected function doReindexFull()
    {
        foreach ($this->getAllRules() as $rule) {
            $this->updateRuleProductData($rule);
        }
    }

    /**
     * Clean by product ids
     *
     * @param array $productIds
     *
     * @return void
     */
    protected function cleanByIds($productIds)
    {
        $query = $this->connection->deleteFromSelect(
            $this->connection
                ->select()
                ->from($this->resource->getTableName(self::MAGEPLAZA_REWARD_CATALOGRULE_PRODUCT_TABLE), 'product_id')
                ->distinct()
                ->where('product_id IN (?)', $productIds),
            $this->resource->getTableName(self::MAGEPLAZA_REWARD_CATALOGRULE_PRODUCT_TABLE)
        );
        $this->connection->query($query);
    }

    /**
     * @param CatalogRule $rule
     * @param Product $product
     *
     * @return $this
     * @throws Exception
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function applyRule(CatalogRule $rule, $product)
    {
        $ruleId = $rule->getId();
        $productEntityId = $product->getId();
        $websiteIds = array_intersect($product->getWebsiteIds(), $rule->getWebsiteIds());

        if (!$rule->validate($product)) {
            return $this;
        }

        $this->connection->delete(
            $this->resource->getTableName(self::MAGEPLAZA_REWARD_CATALOGRULE_PRODUCT_TABLE),
            [
                $this->connection->quoteInto('rule_id = ?', $ruleId),
                $this->connection->quoteInto('product_id = ?', $productEntityId)
            ]
        );

        $customerGroupIds = $rule->getCustomerGroupIds();
        $fromTime = strtotime($rule->getFromDate());
        $toTime = strtotime($rule->getToDate());
        $toTime = $toTime ? $toTime + self::SECONDS_IN_DAY - 1 : 0;
        $sortOrder = (int)$rule->getSortOrder();
        $actionOperator = $rule->getAction();
        $actionAmount = $rule->getDiscountAmount();
        $discountStyle = $rule->getDiscountStyle();
        $pointAmount = $rule->getPointAmount();
        $maxPoints = $rule->getMaxPoints();
        $actionStop = $rule->getStopRulesProcessing();
        $moneyStep = $rule->getMoneyStep();

        $rows = [];
        try {
            foreach ($websiteIds as $websiteId) {
                foreach ($customerGroupIds as $customerGroupId) {
                    $rows[] = [
                        'rule_id' => $ruleId,
                        'from_time' => $fromTime,
                        'to_time' => $toTime,
                        'website_id' => $websiteId,
                        'customer_group_id' => $customerGroupId,
                        'product_id' => $productEntityId,
                        'action' => $actionOperator,
                        'discount_style' => $discountStyle,
                        'discount_amount' => $actionAmount,
                        'point_amount' => $pointAmount,
                        'max_points' => $maxPoints,
                        'action_stop' => $actionStop,
                        'sort_order' => $sortOrder,
                        'money_step' => $moneyStep
                    ];

                    if (count($rows) == $this->batchCount) {
                        $this->insertMultipleData($rows);
                        $rows = [];
                    }
                }
            }

            if (!empty($rows)) {
                $this->insertMultipleData($rows);
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * @param string $tableName
     *
     * @return string
     */
    protected function getTable($tableName)
    {
        return $this->resource->getTableName($tableName);
    }

    /**
     * @param CatalogRule $rule
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function updateRuleProductData(CatalogRule $rule)
    {
        $ruleId = $rule->getId();
        if ($rule->getProductsFilter()) {
            $this->connection->delete(
                $this->getTable(self::MAGEPLAZA_REWARD_CATALOGRULE_PRODUCT_TABLE),
                ['rule_id=?' => $ruleId, 'product_id IN (?)' => $rule->getProductsFilter()]
            );
        } else {
            $this->connection->delete(
                $this->getTable(self::MAGEPLAZA_REWARD_CATALOGRULE_PRODUCT_TABLE),
                $this->connection->quoteInto('rule_id=?', $ruleId)
            );
        }

        if (!$rule->getIsActive()) {
            return $this;
        }

        $websiteIds = $rule->getWebsiteIds();
        if (!is_array($websiteIds)) {
            $websiteIds = explode(',', $websiteIds);
        }
        if (empty($websiteIds)) {
            return $this;
        }

        Profiler::start('__MATCH_PRODUCTS__');
        $productIds = $rule->getMatchingProductIds();
        Profiler::stop('__MATCH_PRODUCTS__');

        $customerGroupIds = $rule->getCustomerGroupIds();
        $fromTime = strtotime($rule->getFromDate());
        $toTime = strtotime($rule->getToDate());
        $toTime = $toTime ? $toTime + self::SECONDS_IN_DAY - 1 : 0;
        $sortOrder = (int)$rule->getSortOrder();
        $actionOperator = $rule->getAction();
        $actionAmount = $rule->getDiscountAmount();
        $discountStyle = $rule->getDiscountStyle();
        $pointAmount = $rule->getPointAmount();
        $maxPoints = $rule->getMaxPoints();
        $actionStop = $rule->getStopRulesProcessing();
        $moneyStep = $rule->getMoneyStep();

        $rows = [];

        foreach ($productIds as $productId => $validationByWebsite) {
            foreach ($websiteIds as $websiteId) {
                if (empty($validationByWebsite[$websiteId])) {
                    continue;
                }
                foreach ($customerGroupIds as $customerGroupId) {
                    $rows[] = [
                        'rule_id' => $ruleId,
                        'from_time' => $fromTime,
                        'to_time' => $toTime,
                        'website_id' => $websiteId,
                        'customer_group_id' => $customerGroupId,
                        'product_id' => $productId,
                        'action' => $actionOperator,
                        'discount_style' => $discountStyle,
                        'discount_amount' => $actionAmount,
                        'point_amount' => $pointAmount,
                        'max_points' => $maxPoints,
                        'action_stop' => $actionStop,
                        'sort_order' => $sortOrder,
                        'money_step' => $moneyStep
                    ];

                    if (count($rows) == $this->batchCount) {
                        $this->insertMultipleData($rows);
                        $rows = [];
                    }
                }
            }
        }

        if (!empty($rows)) {
            $this->insertMultipleData($rows);
        }

        return $this;
    }

    /**
     * @param $rows
     *
     * @return $this
     */
    public function insertMultipleData($rows)
    {
        $this->connection->insertMultiple($this->getTable(self::MAGEPLAZA_REWARD_CATALOGRULE_PRODUCT_TABLE), $rows);

        return $this;
    }

    /**
     * Get active rules
     *
     * @return array
     */
    protected function getActiveRules()
    {
        return $this->ruleCollectionFactory->create()
            ->addFieldToFilter('is_active', 1);
    }

    /**
     * Get active rules
     *
     * @return array
     */
    protected function getAllRules()
    {
        return $this->ruleCollectionFactory->create();
    }

    /**
     * @param int $productId
     *
     * @return Product
     */
    protected function getProduct($productId)
    {
        if (!isset($this->loadedProducts[$productId])) {
            $this->loadedProducts[$productId] = $this->productFactory->create()->load($productId);
        }

        return $this->loadedProducts[$productId];
    }

    /**
     * @param Exception $e
     *
     * @return void
     */
    protected function critical($e)
    {
        $this->logger->critical($e);
    }
}
