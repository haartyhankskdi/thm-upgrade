<?php

namespace Ebizmarts\SagePaySuite\Model\ObjectLoader;

use Ebizmarts\SagePaySuite\Helper\RepositoryQuery;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;

class OrderLoader
{
    /** @var OrderRepository */
    private $orderRepository;

    /** @var RepositoryQuery */
    private $repositoryQuery;

    /** @var Logger */
    private $suiteLogger;

    /**
     * OrderLoader constructor.
     * @param OrderRepository $orderRepository
     * @param RepositoryQuery $repositoryQuery
     * @param Logger $suiteLogger
     */
    public function __construct(
        OrderRepository $orderRepository,
        RepositoryQuery $repositoryQuery,
        Logger $suiteLogger
    ) {
        $this->orderRepository = $orderRepository;
        $this->repositoryQuery = $repositoryQuery;
        $this->suiteLogger = $suiteLogger;
    }

    /**
     * @param $orderId
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($orderId)
    {
        return $this->orderRepository->get($orderId);
    }

    /**
     * @param Quote $quote
     * @return \Magento\Sales\Model\Order
     * @throws LocalizedException
     */
    public function loadOrderFromQuote(Quote $quote)
    {
        $searchCriteria = $this->createSearchCriteria($quote);

        /** @var Order */
        $order = null;
        $orders = $this->orderRepository->getList($searchCriteria);
        $ordersCount = $orders->getTotalCount();

        if ($ordersCount > 0) {
            $orders = $orders->getItems();
            $order = current($orders);
        }

        if ($order === null || $order->getId() === null) {
            $this->suiteLogger->sageLog(
                Logger::LOG_EXCEPTION,
                "Error loading order from quote increment: " . $quote->getReservedOrderId() .
                "\nOrder count: " . $ordersCount,
                [__METHOD__, __LINE__]
            );
            throw new LocalizedException(__("Error loading order."));
        }

        return $order;
    }

    /**
     * @param Quote $quote
     * @return \Magento\Framework\Api\SearchCriteria
     */
    private function createSearchCriteria(Quote $quote)
    {
        $incrementId = $quote->getReservedOrderId();
        $storeId = $quote->getStoreId();

        $incrementIdFilter = [
            'field' => 'increment_id',
            'conditionType' => 'eq',
            'value' => $incrementId
        ];
        $storeIdFilter = [
            'field' => 'store_id',
            'conditionType' => 'eq',
            'value' => $storeId
        ];

        $searchCriteria = $this->repositoryQuery->buildSearchCriteriaWithAND([$incrementIdFilter, $storeIdFilter]);
        return $searchCriteria;
    }

    /**
     * @param int $orderId
     * @return null|OrderInterface
     */
    public function loadOrderById($orderId)
    {
        $order = null;
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (InputException $e) {
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $e->getMessage(), [__METHOD__, __LINE__]);
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $e->getTraceAsString(), [__METHOD__, __LINE__]);
        } catch (NoSuchEntityException $e) {
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $e->getMessage(), [__METHOD__, __LINE__]);
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $e->getTraceAsString(), [__METHOD__, __LINE__]);
        }

        return $order;
    }
}
