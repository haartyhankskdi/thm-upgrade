<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Customer;

use Amasty\Gdpr\Model\ActionLogger;
use Amasty\Gdpr\Controller\Result\FileFactory;
use Amasty\Gdpr\Controller\Result\File;
use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\CustomerData;
use Amasty\Gdpr\Model\GuestOrderProvider;
use Magento\Customer\Model\Authentication;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\AuthenticationException;
use Psr\Log\LoggerInterface;

class DownloadCsv extends Action implements HttpPostActionInterface
{
    public const FILE_NAME = 'personal-data';

    /**
     * @var CustomerData
     */
    private $customerData;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var Authentication
     */
    private $authentication;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var GuestOrderProvider
     */
    private $guestOrderProvider;

    /**
     * @var ActionLogger
     */
    private $actionLogger;

    public function __construct(
        Context $context,
        CustomerData $customerData,
        Session $customerSession,
        LoggerInterface $logger,
        Authentication $authentication,
        FormKeyValidator $formKeyValidator,
        Config $configProvider,
        FileFactory $fileFactory,
        GuestOrderProvider $guestOrderProvider,
        ActionLogger $actionLogger
    ) {
        parent::__construct($context);
        $this->customerData = $customerData;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->formKeyValidator = $formKeyValidator;
        $this->authentication = $authentication;
        $this->configProvider = $configProvider;
        $this->fileFactory = $fileFactory;
        $this->guestOrderProvider = $guestOrderProvider;
        $this->actionLogger = $actionLogger;
    }

    public function execute()
    {
        $errorMessage = '';

        if (!$this->configProvider->isAllowed(Config::DOWNLOAD)) {
            $errorMessage = __('Access denied.');
        }

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $errorMessage = __('Invalid Form Key. Please refresh the page.');
        }

        if ($errorMessage) {
            $this->messageManager->addErrorMessage($errorMessage);
            return $this->resultRedirectFactory->create()->setRefererUrl();
        }

        $customerId = (int)$this->customerSession->getCustomerId();
        $mergeIntoOneFile = (bool)$this->getRequest()->getParam(Config::DOWNLOAD_MERGE_INTO_ONE_FILE);
        try {
            if ($customerId) {
                $customerPass = $this->getRequest()->getParam('current_password');
                $this->authentication->authenticate($customerId, $customerPass);
                $data = $this->customerData->getPersonalData($customerId, $mergeIntoOneFile);
            } else {
                $incrementId = $this->guestOrderProvider->getGuestOrder()->getIncrementId();
                $data = $this->customerData->getGuestPersonalData($incrementId, $mergeIntoOneFile);
            }
        } catch (AuthenticationException $e) {
            $this->messageManager->addErrorMessage(__('Wrong Password. Please recheck it.'));

            return $this->resultRedirectFactory->create()->setRefererUrl();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong.')
            );
            $this->logger->critical($e);

            return $this->resultRedirectFactory->create()->setRefererUrl();
        }

        $action = sprintf('data_downloaded_by_%s', $customerId ? 'customer' : 'guest');
        $this->actionLogger->logAction($action, $customerId);

        return $this->fileFactory->create(
            [
                'fileName' => self::FILE_NAME,
                'fileExtension' => $mergeIntoOneFile ? File::CSV : File::ZIP,
                'data' => $data
            ]
        );
    }
}
