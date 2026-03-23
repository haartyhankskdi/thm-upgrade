<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml\Request;

use Amasty\Gdpr\Controller\Adminhtml\AbstractRequest;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\Collection;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

abstract class RequestProcessAction extends AbstractRequest
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Action\Context $context,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
    }

    /**
     * Process delete requests
     *
     * @param Collection $requests
     * @param array      $customerIds
     * @param \Closure   $action
     *
     * @return int
     */
    protected function processRequests(Collection $requests, $customerIds, \Closure $action)
    {
        foreach ($customerIds as $customerId) {
            try {
                $action($customerId);
                $requests->deleteByCustomerId($customerId);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(__('An error has occurred. Please check logs for more details'));
            }
        }

        return count($customerIds);
    }
}
