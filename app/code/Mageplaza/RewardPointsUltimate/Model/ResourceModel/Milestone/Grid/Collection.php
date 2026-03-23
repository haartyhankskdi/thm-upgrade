<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Model\ResourceModel\Milestone\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Mageplaza\RewardPointsUltimate\Model\MilestoneFactory;
use Mageplaza\RewardPointsUltimate\Model\ResourceModel\Milestone;
use Psr\Log\LoggerInterface as Logger;
use Zend_Db_Expr;

/**
 * Class Collection
 * @package Mageplaza\RewardPointsUltimate\Model\ResourceModel\Milestone\Grid
 */
class Collection extends SearchResult
{
    /**
     * @var MilestoneFactory $milestoneFactory
     */
    protected $milestoneFactory;

    /**
     * Collection constructor.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param MilestoneFactory $milestoneFactory
     * @param string $mainTable
     * @param string $resourceModel
     *
     * @throws LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        MilestoneFactory $milestoneFactory,
        $mainTable = 'mageplaza_reward_milestone',
        $resourceModel = Milestone::class
    ) {
        $this->milestoneFactory = $milestoneFactory;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * @return $this|Collection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ['cus' => $this->getTable('mageplaza_reward_milestone_customer')],
            'main_table.tier_id = cus.tier_id',
            ['customer_id']
        )->columns([
            'number_customer' => new Zend_Db_Expr('COUNT(`cus`.`customer_id`)')
        ])->group('main_table.tier_id');

        $this->addFilterToMap('main_table.tier_id', 'tier_id');

        return $this;
    }

    /**
     * @param array|string $field
     * @param null $condition
     *
     * @return $this|Collection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'tier_id') {
            $resultCondition = $this->_translateCondition($field, $condition);
            $this->getSelect()->having($resultCondition);

            return $this;
        }

        return parent::addFieldToFilter($field, $condition);
    }
}
