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

namespace Mageplaza\RewardPointsUltimate\Cron;

use Exception;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Area;
use Magento\Framework\DataObject;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;
use Psr\Log\LoggerInterface;

/**
 * Class Birthday
 * @package Mageplaza\RewardPointsUltimate\Cron
 */
class Birthday
{
    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var BehaviorFactory
     */
    protected $behaviorFactory;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Birthday constructor.
     *
     * @param DateTime $dateTime
     * @param HelperData $helperData
     * @param CustomerFactory $customerFactory
     * @param BehaviorFactory $behaviorFactory
     * @param TransportBuilder $transportBuilder
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        DateTime $dateTime,
        HelperData $helperData,
        CustomerFactory $customerFactory,
        BehaviorFactory $behaviorFactory,
        TransportBuilder $transportBuilder,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager
    ) {
        $this->dateTime = $dateTime;
        $this->helperData = $helperData;
        $this->customerFactory = $customerFactory;
        $this->behaviorFactory = $behaviorFactory;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->_storeManager = $storeManager;

    }

    /**
     * @return $this
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->helperData->isEnabled()) {
            return $this;
        }

        $customers = $this->customerFactory->create()->getCollection()
            ->addFieldToFilter('confirmation', ['null' => 'confirmation'])
            ->addFieldToFilter('lock_expires', ['null' => 'lock_expires'])
            ->addFieldToFilter('dob', ['notnull' => 'dob']);
        $behavior = $this->behaviorFactory->create();
        foreach ($customers as $customer) {
            $dayMonthNow = date('m') . '-' . date('d');
            $date = explode('-', $customer->getDob());
            $dayMonthBirthday = $date[1] . '-' . $date[2];
            if ($dayMonthBirthday != $dayMonthNow) {
                continue;
            }
            $behavior->setCustomerWebsiteId($customer->getWebsiteId());
            $behavior = $behavior->getBehaviorRuleByAction(
                CustomerEvents::CUSTOMER_BIRTHDAY,
                true,
                $customer->getGroupId()
            );
            $expireAfter    = $behavior->getExpireAfter();
            $pointAmount    = $behavior->getPointAmount();
            if (!$pointAmount) {
                continue;
            }
            $customerHasBirthDay = $behavior->checkCustomerHasBirthday($customer->getId());
            if (!$customerHasBirthDay) {
                try {
                    $transaction = $this->helperData->getTransaction()->createTransaction(
                        HelperData::ACTION_CUSTOMER_BIRTHDAY,
                        $customer,
                        new DataObject([
                            'point_amount'    => $pointAmount,
                            'expireAfter'     => $expireAfter
                        ])
                    );
                    if ($transaction->getId() && $behavior->getIsEnabledEmail()) {
                        $transport = $this->transportBuilder->setTemplateIdentifier($behavior->getEmailTemplate())
                            ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $customer->getStoreId()])
                            ->setTemplateVars(
                                [
                                    'customer_name' => $customer->getName(),
                                    'point_amount' => $transaction->getPointAmount(),
                                    'store_name' => $this->_storeManager->getStore($customer->getStoreId())->getName()
                                ]
                            )
                            ->setFrom($behavior->getSender())
                            ->addTo($customer->getEmail(), $customer->getName())
                            ->getTransport();

                        $transport->sendMessage();
                    }
                } catch (Exception $e) {
                    $this->logger->critical($e->getMessage());
                }
            }
        }
        return $this;
    }
}
