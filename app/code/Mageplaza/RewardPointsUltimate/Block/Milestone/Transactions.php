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
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Block\Milestone;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Mageplaza\RewardPoints\Block\Account\Transaction;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory;
use Mageplaza\RewardPointsUltimate\Helper\Data as UltimateData;

/**
 * Class Transactions
 * @package Mageplaza\RewardPointsUltimate\Block\Milestone
 */
class Transactions extends Transaction
{
    /**
     * @var UltimateData
     */
    protected $ultimateData;

    /**
     * Transactions constructor.
     *
     * @param Template\Context $context
     * @param Data $helper
     * @param Session $customerSession
     * @param UltimateData $ultimateData
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $helper,
        Session $customerSession,
        UltimateData $ultimateData,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->ultimateData = $ultimateData;
        parent::__construct(
            $context,
            $helper,
            $customerSession,
            $collectionFactory,
            $data
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $actionCode = $this->ultimateData->getSourceMilestoneAction();

        $this->getTransactions()->addFieldToFilter('action_code', ['in' => $actionCode]);
        if ($this->ultimateData->getPeriodDate()) {
            $this->getTransactions()->addFieldToFilter('created_at', ['gteq' => $this->ultimateData->getPeriodDate()]);
        }
    }

    /**
     * @return bool
     */
    public function getIsRecent()
    {
        return true;
    }
}
