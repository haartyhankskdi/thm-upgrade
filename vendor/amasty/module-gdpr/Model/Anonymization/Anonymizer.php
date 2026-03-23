<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Anonymization;

use Amasty\Gdpr\Api\Data\DeleteRequestInterface;
use Amasty\Gdpr\Api\RequestInterface;
use Amasty\Gdpr\Model\ActionLogger;
use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\Notification\NotificationsApplier;
use Amasty\Gdpr\Model\Notification\NotifiersProvider;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\CollectionFactory as RequestCollectionFactory;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Event\Manager;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class Anonymizer implements RequestInterface
{
    /**
     * @var TypePool
     */
    private $typePool;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var RequestCollectionFactory
     */
    private $deleteRequestCollectionFactory;

    /**
     * @var ActionLogger
     */
    private $logger;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var Manager
     */
    private $eventManager;

    /**
     * @var NotificationsApplier
     */
    private $notificationsApplier;

    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        RequestCollectionFactory $deleteRequestCollectionFactory,
        ActionLogger $logger,
        Config $configProvider,
        ProductMetadataInterface $productMetadata,
        Manager $eventManager,
        TypePool $typePool,
        NotificationsApplier $notificationsApplier
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->deleteRequestCollectionFactory = $deleteRequestCollectionFactory;
        $this->logger = $logger;
        $this->configProvider = $configProvider;
        $this->productMetadata = $productMetadata;
        $this->eventManager = $eventManager;
        $this->typePool = $typePool;
        $this->notificationsApplier = $notificationsApplier;
    }

    public function approveDeleteRequest(int $customerId): bool
    {
        if (!$this->canDeleteCustomer($customerId)) {
            return false;
        }

        $this->anonymizeCustomer($customerId, true);
        $this->deleteRequestCollectionFactory->create()->approveRequest($customerId);
        $this->logger->logAction('delete_request_approved', $customerId);

        return true;
    }

    public function deleteExpiredItems(int $customerId): void
    {
        $expiredItemsAnonymizerTypes = [AbstractType::TYPE_ORDER, AbstractType::TYPE_QUOTES];

        foreach ($expiredItemsAnonymizerTypes as $type) {
            if ($anonymizer = $this->typePool->get($type)) {
                $anonymizer->setIsDeleting(true);
                $anonymizer->execute($customerId);
            }
        }
    }

    private function canDeleteCustomer(int $customerId): bool
    {
        $ordersData = $this->getCustomerActiveOrders($customerId);

        return empty($ordersData);
    }

    public function getCustomerActiveOrders(int $customerId): array
    {
        $ordersData = [];

        if ($this->configProvider->isAvoidAnonymization()) {
            $orderStatuses = $this->configProvider->getOrderStatuses();

            if ($orderStatuses) {
                $orders = $this->orderCollectionFactory->create()
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('status', ['in' => $orderStatuses]);

                $ordersData = $orders->getData();
            }
        }

        return $ordersData;
    }

    public function getUnprocessedRequests(): array
    {
        $requestCollection = $this->deleteRequestCollectionFactory->create()->addFieldToFilter(
            DeleteRequestInterface::APPROVED,
            false
        );

        return $requestCollection->getData();
    }

    public function denyDeleteRequest(int $customerId, string $comment): void
    {
        $this->notificationsApplier->apply(
            NotifiersProvider::EVENT_DENY_DELETION,
            $customerId,
            ['comment' => $comment]
        );
        $this->deleteRequestCollectionFactory->create()->deleteByCustomerId($customerId);
    }

    public function anonymizeCustomer(int $customerId, $isDeleting = false): void
    {
        $this->eventManager->dispatch(
            'before_amgdpr_customer_anonymisation',
            ['customerId' => $customerId, 'isDeleting' => $isDeleting]
        );

        foreach ($this->typePool->getAll() as $anonymizer) {
            $anonymizer->setIsDeleting($isDeleting);
            $anonymizer->execute($customerId);
        }

        if (!$isDeleting) {
            $this->logger->logAction('data_anonymised_by_customer', $customerId);
        }

        $this->eventManager->dispatch(
            'after_amgdpr_customer_anonymisation',
            ['customerId' => $customerId, 'isDeleting' => $isDeleting]
        );
    }

    public function anonymizeOrder(string $incrementId): bool
    {
        $anonymizer = $this->typePool->get(AbstractType::TYPE_ORDER);

        if ($anonymizer) {
            return $anonymizer->anonymizeOrderByIncrementId($incrementId);
        }

        return false;
    }
}
