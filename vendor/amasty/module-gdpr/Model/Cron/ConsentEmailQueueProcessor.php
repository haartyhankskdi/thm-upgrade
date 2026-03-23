<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Cron;

use Amasty\Gdpr\Api\ConsentQueueRepositoryInterface;
use Amasty\Gdpr\Api\Data\ConsentQueueInterface;
use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\ConsentQueue;
use Amasty\Gdpr\Model\ConsentQueue\ConsentQueueManager;
use Amasty\Gdpr\Model\CountryRestrictions\Validator as CountryValidator;
use Amasty\Gdpr\Model\Notification\NotificationsApplier;
use Amasty\Gdpr\Model\Notification\NotifiersProvider;
use Amasty\Gdpr\Model\ResourceModel\ConsentQueue\Collection;
use Amasty\Gdpr\Model\ResourceModel\ConsentQueue\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;

class ConsentEmailQueueProcessor
{
    private const BATCH_SIZE = 40;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConsentQueueRepositoryInterface
     */
    private $consentQueueRepository;

    /**
     * @var ConsentQueueManager
     */
    private $consentQueueManager;

    /**
     * @var NotificationsApplier
     */
    private $notificationsApplier;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var CountryValidator
     */
    private $countryValidator;

    public function __construct(
        CollectionFactory $collectionFactory,
        LoggerInterface $logger,
        Config $configProvider,
        ConsentQueueRepositoryInterface $consentQueueRepository,
        ConsentQueueManager $consentQueueManager,
        NotificationsApplier $notificationsApplier,
        ?CountryValidator $countryValidator = null
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
        $this->configProvider = $configProvider;
        $this->consentQueueRepository = $consentQueueRepository;
        $this->consentQueueManager = $consentQueueManager;
        $this->notificationsApplier = $notificationsApplier;
        // OM for backward compatibility
        $this->countryValidator = $countryValidator ?? ObjectManager::getInstance()->get(CountryValidator::class);
    }

    public function execute(): void
    {
        if (!$this->configProvider->isModuleEnabled()
            || !$this->configProvider->isPolicyChangeNotificationEnabled()
        ) {
            return;
        }

        /** @var Collection $consentQueueCollection */
        $consentQueueCollection = $this->collectionFactory->create();
        $consentQueueCollection
            ->addFilter(ConsentQueueInterface::STATUS, ConsentQueue::STATUS_PENDING)
            ->addCustomerIp()
            ->addCustomerStore()
            ->setPageSize(self::BATCH_SIZE);
        $timeLastRegenerate = $this->consentQueueManager->getTimeLastRegenerate();
        foreach ($consentQueueCollection->getItems() as $queueItem) {
            if ($timeLastRegenerate != $this->consentQueueManager->getTimeLastRegenerate()) {
                break;
            }
            if ($this->countryValidator->isCountryAllowed($queueItem->getStoreId(), $queueItem->getIp())) {
                $this->sendEmail($queueItem);
            }
        }
    }

    private function sendEmail(ConsentQueueInterface $queueItem): void
    {
        $result = $this->notificationsApplier->apply(
            NotifiersProvider::EVENT_POLICY_CHANGE,
            (int)$queueItem->getCustomerId()
        );

        try {
            $status = ConsentQueue::STATUS_SUCCESS;
            if (!$result) {
                $status = ConsentQueue::STATUS_FAIL;
            }

            $queueItem->setStatus($status);
            $this->consentQueueRepository->save($queueItem);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
    }
}
