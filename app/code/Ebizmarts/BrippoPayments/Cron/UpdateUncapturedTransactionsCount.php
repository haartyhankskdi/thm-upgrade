<?php

namespace Ebizmarts\BrippoPayments\Cron;

use Ebizmarts\BrippoPayments\Model\UncapturedPayments;
use Magento\Framework\Exception\NoSuchEntityException;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\Stats as BrippoApi;
use Ebizmarts\BrippoPayments\Model\UncapturedPaymentsFactory;
use Ebizmarts\BrippoPayments\Model\ResourceModel\UncapturedPayments as UncapturedPaymentsResource;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;


class UpdateUncapturedTransactionsCount
{
    /** @var Logger */
    protected $logger;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var WriterInterface */
    protected $configWriter;

    /** @var DataHelper */
    protected $dataHelper;

    /** @var BrippoApi */
    protected $brippoApi;

    /** @var UncapturedPaymentsFactory */
    protected $uncapturedPaymentsFactory;

    /** @var UncapturedPaymentsResource */
    protected $uncapturedPaymentsResource;

    /**
     * UpdateUncapturedTransactionsCount constructor.
     * @param Logger $logger
     * @param StoreManagerInterface $storeManager
     * @param DataHelper $dataHelper
     * @param BrippoApi $brippoApi
     * @param WriterInterface $configWriter
     */
    public function __construct(
        Logger $logger,
        StoreManagerInterface $storeManager,
        DataHelper $dataHelper,
        BrippoApi $brippoApi,
        WriterInterface $configWriter,
        UncapturedPaymentsFactory $uncapturedPaymentsFactory,
        UncapturedPaymentsResource $uncapturedPaymentsResource
    ) {
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->brippoApi = $brippoApi;
        $this->configWriter = $configWriter;
        $this->uncapturedPaymentsFactory = $uncapturedPaymentsFactory;
        $this->uncapturedPaymentsResource = $uncapturedPaymentsResource;
    }

    /**
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $accounts = [];
        foreach ($this->storeManager->getStores() as $store) {
            if (!$this->dataHelper->getStoreConfig(DataHelper::NOTIFY_UNCAPTURED_TRANSACTIONS_ACTIVE_CONFIG_PATH, $store->getId())) {
                return;
            }
            $isLiveMode = $this->dataHelper->isLiveMode($store->getId());
            $accountId = $this->dataHelper->getAccountId($store->getId(), $isLiveMode);

            if (empty($accountId)) {
                continue;
            }
            $account['id'] = $accountId;
            $account['store_id'] = $store->getId();
            $account['mode'] = $isLiveMode;

            $alreadyAdded = array_filter($accounts, function ($acc) use ($account) {
                return $acc['id'] === $account['id'];
            });

            if (empty($alreadyAdded)) {
                $accounts[] = $account;
            }
        }

        foreach ($accounts as $account) {
            $apiResponse = $this->brippoApi->getUncapturedPaymentsCount($account['id'], $account['mode']);

            if (isset($apiResponse['total_uncaptured'])) {
                $this->saveUncapturedTransactionsCount(
                    $account['store_id'],
                    $apiResponse['total_uncaptured']
                );
            }
        }
    }

    /**
     * @param int $storeId
     * @param int $count
     */
    public function saveUncapturedTransactionsCount(
        int $storeId,
        int $count
    ): void {
        /** @var UncapturedPayments $uncapturedPayments */
        $uncapturedPayments = $this->uncapturedPaymentsFactory->create();
        $uncapturedPayments->setStoreId($storeId);
        $uncapturedPayments->setCount($count);

        $this->uncapturedPaymentsResource->update($uncapturedPayments);
    }
}
