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

namespace Mageplaza\RewardPointsPro\Block\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Mageplaza\RewardPointsPro\Helper\Data;
use Mageplaza\RewardPoints\Model\Account;
use Mageplaza\RewardPoints\Model\AccountFactory;
use Mageplaza\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory as TransactionCollectionFactory;
use Mageplaza\RewardPoints\Model\Source\Status;

/**
 * Class Dashboard
 * @method setAccount($account)
 * @method Account getAccount()
 * @package Mageplaza\RewardPoints\Block\Account
 */
class Dashboard extends \Mageplaza\RewardPoints\Block\Account\Dashboard
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var AccountFactory
     */
    protected $accountFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var TransactionCollectionFactory
     */
    protected $transactionCollectionFactory;

    /**
     * Dashboard constructor.
     *
     * @param Session $customerSession
     * @param AccountFactory $accountFactory
     * @param TransactionCollectionFactory $transactionCollectionFactory
     * @param Template\Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Session $customerSession,
        AccountFactory $accountFactory,
        TransactionCollectionFactory $transactionCollectionFactory,
        Template\Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->customerSession              = $customerSession;
        $this->accountFactory               = $accountFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;

        parent::__construct($context, $helper, $data);
    }

    /**
     * @param $storeId
     *
     * @return mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTotalHoldPoints($storeId = null)
    {
        $account    = $this->accountFactory->create()->loadByCustomerId($this->customerSession->getCustomerId());
        $holdPoints = $this->getTotalPointsTypeHolding($account, Status::HOLDING);

        return $this->helper->getPointHelper()->format($holdPoints, false, $storeId);
    }

    /**
     * @param $storeId
     *
     * @return bool
     */
    public function checkEnableHoldingBlock($storeId = null)
    {
        $holdingConfig = $this->helper->getConfigEarning('hold_points', $storeId);

        $account    = $this->accountFactory->create()->loadByCustomerId($this->customerSession->getCustomerId());
        $holdPoints = $this->getTotalPointsTypeHolding($account, Status::HOLDING);

        if ($holdingConfig) {
            return true;
        } else if ($holdPoints) {
            return true;
        }

        return false;
    }

    /**
     * @param $account
     * @param $type
     *
     * @return int
     */
    protected function getTotalPointsTypeHolding($account, $type)
    {
        $total = 0;

        $transactionCollectionFactory = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('status', $type)
            ->addFieldToFilter('action_type', Data::ACTION_TYPE_HOLDING)
            ->addFieldToFilter('reward_id', $account->getId());

        foreach ($transactionCollectionFactory as $item) {
            $total += $item->getPointAmount();
        }

        return $total;
    }
}
