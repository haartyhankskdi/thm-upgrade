<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Adminhtml\Reports\Tokens;

use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\Token\VaultDetailsHandler;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Ebizmarts_SagePaySuite::token_report_delete';

    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var VaultDetailsHandler
     */
    private $vaultDetailsHandler;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param Logger $suiteLogger
     * @param \Psr\Log\LoggerInterface $logger
     * @param VaultDetailsHandler $vaultDetailsHandler
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Logger $suiteLogger,
        \Psr\Log\LoggerInterface $logger,
        VaultDetailsHandler $vaultDetailsHandler
    ) {

        parent::__construct($context);
        $this->suiteLogger = $suiteLogger;
        $this->logger      = $logger;
        $this->vaultDetailsHandler = $vaultDetailsHandler;
    }

    public function execute()
    {
        try {
            $this->_view->loadLayout();
            $tokenId = $this->getRequest()->getParam('id');
            $customerId = $this->getRequest()->getParam('customer_id');

            if (empty($tokenId) || empty($customerId)) {
                throw new \Magento\Framework\Validator\Exception(__('Unable to delete token: Invalid token id.'));
            }
            //delete
            $this->vaultDetailsHandler->deleteToken($tokenId, $customerId);

            $this->messageManager->addSuccessMessage(__('Token deleted successfully.'));
        } catch (\Ebizmarts\SagePaySuite\Model\Api\ApiException $apiException) {
            $this->logger->critical($apiException);
            $this->messageManager->addErrorMessage(__($apiException->getUserMessage()));
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        $this->_redirect('*/*/index');
    }
}
