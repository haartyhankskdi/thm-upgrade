<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml\Policy;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Controller\Adminhtml\AbstractPolicy;
use Amasty\Gdpr\Model\PolicyFactory;
use Amasty\Gdpr\Model\ResourceModel\Policy\Collection;
use Amasty\Gdpr\Model\ResourceModel\Policy\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassDelete extends AbstractPolicy
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CollectionFactory
     */
    private $policyCollectionFactory;

    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        LoggerInterface $logger,
        CollectionFactory $policyCollectionFactory,
        PolicyRepositoryInterface $policyRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->logger = $logger;
        $this->policyCollectionFactory = $policyCollectionFactory;
        $this->policyRepository = $policyRepository;
    }

    /**
     * Mass action execution
     *
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->filter->applySelectionOnTargetProvider(); // compatibility with Mass Actions on Magento 2.1.0
        /** @var Collection $collection */
        $collection = $this->filter->getCollection($this->policyCollectionFactory->create());
        $deletedPolices = 0;

        if ($collection->count() > 0) {
            try {
                foreach ($collection->getItems() as $policy) {
                    if ($policy->getStatus() != \Amasty\Gdpr\Model\Policy::STATUS_ENABLED) {
                        $this->policyRepository->delete($policy);
                        $deletedPolices++;
                    }
                }

                $this->messageManager->addSuccessMessage(
                    __('%1 policies(s) has been successfully deleted', $deletedPolices)
                );

            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error has occurred'));
                $this->logger->critical($e);
            }
        }

        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
