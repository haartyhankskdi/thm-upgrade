<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml\Consents;

use Amasty\Gdpr\Controller\Adminhtml\AbstractConsents;
use Amasty\Gdpr\Model\Consent\Consent;
use Amasty\Gdpr\Model\Consent\Repository;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Delete extends AbstractConsents
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Repository
     */
    private $repository;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        Repository $repository
    ) {
        parent::__construct($context);

        $this->logger = $logger;
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam(Consent::ID);

        if ($id) {
            try {
                $this->repository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You have successfully deleted the consent'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete item right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
            }
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
