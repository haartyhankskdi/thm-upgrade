<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml\ConsentQueue;

use Amasty\Gdpr\Controller\Adminhtml\AbstractConsentQueue;
use Amasty\Gdpr\Model\ConsentQueue\ConsentQueueManager;
use Amasty\Gdpr\Model\ResourceModel\ConsentQueue\Collection;
use Amasty\Gdpr\Model\ResourceModel\ConsentQueue\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;

class MassResend extends AbstractConsentQueue
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $consentQueueCollectionFactory;

    /**
     * @var ConsentQueueManager
     */
    private $consentQueueManager;

    public function __construct(
        Filter $filter,
        Action\Context $context,
        CollectionFactory $consentQueueCollectionFactory,
        ConsentQueueManager $consentQueueManager
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->consentQueueCollectionFactory = $consentQueueCollectionFactory;
        $this->consentQueueManager = $consentQueueManager;
    }

    public function execute()
    {
        $this->filter->applySelectionOnTargetProvider();
        /** @var Collection $collection */
        $collection = $this->filter->getCollection($this->consentQueueCollectionFactory->create());

        try {
            $this->messageManager->addSuccessMessage(
                __(
                    '%1 customer(s) has been successfully added to email queue',
                    $this->consentQueueManager->resetQueueItems($collection->getAllIds())
                )
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('*/*');
    }
}
