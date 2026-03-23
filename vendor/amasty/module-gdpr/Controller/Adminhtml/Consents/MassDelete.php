<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml\Consents;

use Amasty\Gdpr\Controller\Adminhtml\AbstractConsents;
use Amasty\Gdpr\Model\Consent\Repository;
use Amasty\Gdpr\Model\Consent\ResourceModel\CollectionFactory;
use Amasty\Gdpr\Model\Consent\ResourceModel\Collection;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassDelete extends AbstractConsents
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Repository $repository,
        Filter $filter,
        LoggerInterface $logger
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
        $this->filter = $filter;
        $this->logger = $logger;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            /** @var Collection $collection **/
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            foreach ($collection as $consent) {
                $this->repository->delete($consent);
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error has occurred'));
            $this->logger->critical($e);
        }

        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
