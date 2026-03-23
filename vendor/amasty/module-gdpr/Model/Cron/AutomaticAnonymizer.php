<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Cron;

use Amasty\Gdpr\Api\Data\DeleteRequestInterface;
use Amasty\Gdpr\Api\DeleteRequestRepositoryInterface;
use Amasty\Gdpr\Model\Anonymization\Anonymizer;
use Amasty\Gdpr\Model\CleaningDate;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\CollectionFactory as DeleteRequestsCollectionFactory;

class AutomaticAnonymizer
{
    /**
     * @var CleaningDate
     */
    private $cleaningDate;

    /**
     * @var DeleteRequestsCollectionFactory
     */
    private $deleteRequestsCollectionFactory;

    /**
     * @var Anonymizer
     */
    private $anonymizer;

    /**
     * @var DeleteRequestRepositoryInterface
     */
    private $deleteRequestRepository;

    public function __construct(
        CleaningDate $cleaningDate,
        DeleteRequestsCollectionFactory $deleteRequestsCollectionFactory,
        Anonymizer $anonymizer,
        DeleteRequestRepositoryInterface $deleteRequestRepository
    ) {
        $this->cleaningDate = $cleaningDate;
        $this->deleteRequestsCollectionFactory = $deleteRequestsCollectionFactory;
        $this->anonymizer = $anonymizer;
        $this->deleteRequestRepository = $deleteRequestRepository;
    }

    public function requestProcess()
    {
        if (!$dateForRemove = $this->cleaningDate->getPersonalDataStoredDate()) {
            return null;
        }

        $requestsCollection = $this->deleteRequestsCollectionFactory->create();
        $requestsCollection->addFieldToSelect(DeleteRequestInterface::CUSTOMER_ID);
        $requestsCollection->addFieldToFilter(
            DeleteRequestInterface::APPROVED,
            ['eq' => \Amasty\Gdpr\Model\DeleteRequest::IS_APPROVED]
        );

        foreach ($requestsCollection as $request) {
            $this->anonymizer->deleteExpiredItems((int)$request->getCustomerId());
        }
    }
}
