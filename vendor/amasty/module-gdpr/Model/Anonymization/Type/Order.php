<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Anonymization\Type;

use Amasty\Gdpr\Model\Anonymization\AbstractType;
use Amasty\Gdpr\Model\CleaningDate;
use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\CustomerData;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\GridPool;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class Order extends AbstractType
{
    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CleaningDate
     */
    protected $cleaningDate;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var GridPool
     */
    private $gridPool;

    public function __construct(
        CollectionFactory $customerCollectionFactory,
        CustomerData $customerData,
        CleaningDate $cleaningDate,
        OrderCollectionFactory $orderCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        Config $configProvider,
        GridPool $gridPool
    ) {
        parent::__construct($customerCollectionFactory, $customerData);
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->cleaningDate = $cleaningDate;
        $this->configProvider = $configProvider;
        $this->gridPool = $gridPool;
    }

    public function execute(int $customerId)
    {
        /** @var OrderCollection $entities */
        $orders = $this->orderCollectionFactory->create();
        $orders->addFieldToSelect('*')->addFieldToFilter('customer_id', $customerId);

        if ($this->isDeleting && $dateForRemove = $this->cleaningDate->getPersonalDataStoredDate()) {
            // check $this->isDeleting for anonymize all docs after anonymization request
            $orders->addFieldToFilter(OrderInterface::CREATED_AT, ['lt' => $dateForRemove]);
        }

        /** @var \Magento\Sales\Model\Order $item */
        foreach ($orders as $item) {
            if ($this->isAlreadyAnonymized($item)) {
                continue;
            }

            $this->prepareSalesData($item);
            $this->orderRepository->save($item);
            $this->gridPool->refreshByOrderId($item->getId());
        }
    }

    public function anonymizeOrderByIncrementId(string $incrementId): bool
    {
        /** @var OrderCollection $entities */
        $orders = $this->orderCollectionFactory->create();
        $item = $orders->addFieldToSelect('*')
            ->addFieldToFilter(OrderInterface::CUSTOMER_IS_GUEST, 1)
            ->addFieldToFilter(OrderInterface::INCREMENT_ID, $incrementId)
            ->getFirstItem();

        if ($this->configProvider->isAvoidAnonymization()) {
            $orderStatuses = $this->configProvider->getOrderStatuses();

            if (in_array($item->getStatus(), $orderStatuses)) {
                return false;
            }
        }

        $this->prepareSalesData($item);
        $this->orderRepository->save($item);
        $this->gridPool->refreshByOrderId($item->getId());

        return true;
    }
}
