<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml\Policy;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Controller\Adminhtml\AbstractPolicy;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Delete extends AbstractPolicy
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    public function __construct(
        Action\Context $context,
        LoggerInterface $logger,
        PolicyRepositoryInterface $policyRepository
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->policyRepository = $policyRepository;
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');

        if ($id) {
            try {
                $this->policyRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the policy.'));
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
