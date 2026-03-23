<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Anonymization\Type;

use Amasty\Gdpr\Model\Anonymization\AbstractType;
use Amasty\Gdpr\Model\CleaningDate;
use Amasty\Gdpr\Model\CustomerData;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Sales\Api\Data\OrderInterface;

class Quotes extends AbstractType
{
    /**
     * @var CleaningDate
     */
    protected $cleaningDate;

    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    public function __construct(
        CollectionFactory $customerCollectionFactory,
        CustomerData $customerData,
        CleaningDate $cleaningDate,
        QuoteCollectionFactory $quoteCollectionFactory,
        CartRepositoryInterface $quoteRepository
    ) {
        parent::__construct($customerCollectionFactory, $customerData);
        $this->cleaningDate = $cleaningDate;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->quoteRepository = $quoteRepository;
    }

    public function execute(int $customerId)
    {
        /** @var QuoteCollection $entities */
        $quotes = $this->quoteCollectionFactory->create();
        $quotes->addFieldToSelect('*')->addFieldToFilter('customer_id', $customerId);

        if ($this->isDeleting && $dateForRemove = $this->cleaningDate->getPersonalDataStoredDate()) {
            // check $this->isDeleting for anonymize all docs after anonymization request
            $quotes->addFieldToFilter(OrderInterface::CREATED_AT, ['lt' => $dateForRemove]);
        }

        /** @var \Magento\Quote\Model\Quote $item */
        foreach ($quotes as $item) {
            if ($this->isAlreadyAnonymized($item)) {
                continue;
            }
            $this->prepareSalesData($item);
            $this->quoteRepository->save($item);
        }
    }
}
