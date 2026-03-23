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
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Cron;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\ScopeInterface;
use Mageplaza\RewardPoints\Model\Account;
use Mageplaza\RewardPoints\Model\Source\Status;
use Mageplaza\RewardPoints\Model\Transaction;
use Mageplaza\RewardPoints\Model\ResourceModel\Transaction\Collection;
use Mageplaza\RewardPointsPro\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class EarningHoldPoints
 * @package Mageplaza\RewardPointsPro\Cron
 */
class EarningHoldPoints
{
    /**
     * Date model
     *
     * @var DateTime
     */
    protected $date;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Collection
     */
    protected $transactionCollection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * EarningHoldPoints constructor.
     *
     * @param DateTime $date
     * @param Data $helperData
     * @param Collection $transactionCollection
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     */
    public function __construct(
        DateTime $date,
        Data $helperData,
        Collection $transactionCollection,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter
    ) {
        $this->date                  = $date;
        $this->helperData            = $helperData;
        $this->transactionCollection = $transactionCollection;
        $this->logger                = $logger;
        $this->scopeConfig           = $scopeConfig;
        $this->configWriter          = $configWriter;
    }

    /**
     * @return void
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $transactionConfig = $this->scopeConfig->isSetFlag(Data::CONFIG_TRANS_HOLD_PATH, ScopeInterface::SCOPE_WEBSITE);
        if (!$this->helperData->isEnabled() || !$transactionConfig) {
            return;
        }

        $transactions = $this->transactionCollection->addFieldToFilter('status', Status::HOLDING)
            ->addFieldToFilter('action_type', Data::ACTION_TYPE_HOLDING)
            ->addFieldToFilter('holding_date', ['notnull' => true])
            ->addFieldToFilter('expiration_date', ['lteq' => $this->date->date()]);
        if (!$transactions->getSize()) {
            $this->configWriter->save(Data::CONFIG_TRANS_HOLD_PATH, 0, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
            return;
        }

        /** @var Transaction $transaction */
        foreach ($transactions as $transaction) {
            $pointAmount = $transaction->getPointAmount();
            $transaction->setActionCode(Data::ACTION_EARNING_ORDER)
                ->setStatus(Status::COMPLETED)
                ->setHoldingDate(null);

            if ($expireAfter = $this->helperData->getSalesPointExpiredAfter($transaction->getStoreId())) {
                $transaction->setExpirationDate(
                    $this->helperData->getExpirationDate($expireAfter, $transaction->getStoreId())
                );
            }

            /** @var Account $account */
            $account = $this->helperData->getAccountHelper()->create($transaction->getCustomerId());
            $account->addBalance($pointAmount);
            try {
                $transaction->save();
                $account->save();
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
