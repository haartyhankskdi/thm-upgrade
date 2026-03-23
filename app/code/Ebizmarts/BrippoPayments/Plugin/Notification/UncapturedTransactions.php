<?php

namespace Ebizmarts\BrippoPayments\Plugin\Notification;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Ebizmarts\BrippoPayments\Model\UncapturedPaymentsFactory;
use Ebizmarts\BrippoPayments\Model\ResourceModel\UncapturedPayments as UncapturedPaymentsResource;
use Magento\Store\Model\StoreManagerInterface;

class UncapturedTransactions
{
    /** @var Logger */
    protected $logger;

    /** @var DataHelper */
    protected $dataHelper;

    /** @var MessageManagerInterface */
    protected $messageManager;

    /** @var UncapturedPaymentsFactory */
    protected $uncapturedPaymentsFactory;

    /** @var UncapturedPaymentsResource */
    protected $uncapturedPaymentsResource;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /**
     * UncapturedTransactions constructor.
     * @param Logger $logger
     * @param DataHelper $dataHelper
     * @param MessageManagerInterface $messageManager
     * @param UncapturedPaymentsFactory $uncapturedPaymentsFactory
     * @param UncapturedPaymentsResource $uncapturedPaymentsResource
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Logger $logger,
        DataHelper $dataHelper,
        MessageManagerInterface $messageManager,
        UncapturedPaymentsFactory $uncapturedPaymentsFactory,
        UncapturedPaymentsResource $uncapturedPaymentsResource,
        StoreManagerInterface $storeManager
    ) {
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->messageManager = $messageManager;
        $this->uncapturedPaymentsFactory = $uncapturedPaymentsFactory;
        $this->uncapturedPaymentsResource = $uncapturedPaymentsResource;
        $this->storeManager = $storeManager;
    }

    public function beforeDispatch(
        \Magento\Backend\Controller\Adminhtml\Dashboard $subject,
        RequestInterface $request
    ) {

        try {
            foreach ($this->storeManager->getStores() as $store) {
                $uncapturedCount = $this->uncapturedPaymentsFactory->create();
                $this->uncapturedPaymentsResource->load($uncapturedCount, $store->getId(),"store_id");

                if ($uncapturedCount->getCount() > 0) {
                    $isLiveMode = $this->dataHelper->isLiveMode($store->getId());
                    $accountId = $this->dataHelper->getAccountId($store->getId(), $isLiveMode);
                    $this->messageManager->addComplexWarningMessage(
                        'brippoUncapturedTransactions',
                        [
                            'account_id' => $accountId,
                            'total_uncaptured' => $uncapturedCount->getCount()
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            $this->logger->log("Couldn't show uncaptured transactions, error: " . $e->getMessage());
        }
    }
}
