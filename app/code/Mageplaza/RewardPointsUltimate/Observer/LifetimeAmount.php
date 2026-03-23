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

namespace Mageplaza\RewardPointsUltimate\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Invoice;
use Mageplaza\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;

/**
 * Class LifetimeAmount
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class LifetimeAmount implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var BehaviorFactory
     */
    protected $behaviorFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * LifetimeAmount constructor.
     *
     * @param HelperData $helperData
     * @param CustomerRepositoryInterface $customerRepository
     * @param BehaviorFactory $behaviorFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        HelperData $helperData,
        CustomerRepositoryInterface $customerRepository,
        BehaviorFactory $behaviorFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->helperData         = $helperData;
        $this->customerRepository = $customerRepository;
        $this->behaviorFactory    = $behaviorFactory;
        $this->collectionFactory  = $collectionFactory;
    }

    /**
     * @param EventObserver $observer
     *
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        /* @var $invoice Invoice */
        $invoice             = $observer->getEvent()->getInvoice();
        $order               = $invoice->getOrder();
        $behavior            = $this->behaviorFactory->create()->getBehaviorRuleByAction(CustomerEvents::LIFETIME);
        $totalInvoicedAmount = $behavior->getData('total_invoiced_amount');
        $invoiceTotal        = 0;
        foreach ($order->getInvoiceCollection() as $inv) {
            $invoiceTotal += $inv->getGrandTotal();
        }
        if ($behavior->getRuleId() && $invoiceTotal >= $totalInvoicedAmount) {
            $pointLifetimeAmount = $behavior->getPointAmount();
            //$customer            = $this->customerRepository->getById($order->getCustomerId());
               $customerId = $order->getCustomerId();
              if (!$customerId) { 
                return;
               }
               try {
    $customer = $this->customerRepository->getById($customerId);
    } catch (\Exception $e) {
    return;
     }
            $transactions = $this->collectionFactory->create()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('order_id', $order->getId())
                ->addFieldToFilter('action_code', HelperData::ACTION_LIFETIME_AMOUNT);
            if (count($transactions) === 0) {
                $this->helperData->getTransaction()->createTransaction(
                    HelperData::ACTION_LIFETIME_AMOUNT,
                    $customer,
                    new DataObject(
                        [
                            'point_amount' => $pointLifetimeAmount,
                            'store_id'     => $order->getStoreId(),
                            'order_id'     => $order->getId()
                        ]
                    )
                );
            }
        }
    }
}
