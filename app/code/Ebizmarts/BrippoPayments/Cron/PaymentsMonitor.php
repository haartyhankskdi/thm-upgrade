<?php

namespace Ebizmarts\BrippoPayments\Cron;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;

class PaymentsMonitor
{
    protected $logger;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Logger $logger
     * @param DataHelper $dataHelper
     * @param EventManager $eventManager
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Logger $logger,
        DataHelper $dataHelper,
        EventManager $eventManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->eventManager = $eventManager;
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        if (!$this->dataHelper->getStoreConfig(DataHelper::XML_PATH_MONITOR)) {
            return;
        }

        $dateFrom = date('Y-m-d H:i:s', strtotime('now - 41 minutes'));
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('created_at', $dateFrom, 'gteq')
            ->addFilter('created_at', date('Y-m-d H:i:s', strtotime("now -10 minutes")), 'lt')
            ->create();
        $orders = $this->orderRepository->getList($searchCriteria)->getItems();

        foreach ($orders as $order) {
            $this->eventManager->dispatch('brippo_payments_monitor', ['order' => $order]);
        };
    }
}
