<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Customer;

use Amasty\Gdpr\Api\DeleteRequestRepositoryInterface;
use Amasty\Gdpr\Model\ActionLogger;
use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\DeleteRequest;
use Amasty\Gdpr\Model\DeleteRequest\DeleteRequestSource;
use Amasty\Gdpr\Model\DeleteRequestFactory;
use Amasty\Gdpr\Model\GiftRegistryProvider;
use Amasty\Gdpr\Model\Notification\NotificationsApplier;
use Amasty\Gdpr\Model\Notification\NotifiersProvider;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\CollectionFactory;
use Magento\Customer\Controller\AbstractAccount as AbstractAccountAction;
use Magento\Customer\Model\Authentication;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Psr\Log\LoggerInterface;

class AddDeleteRequest extends AbstractAccountAction
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DeleteRequestRepositoryInterface
     */
    private $deleteRequestRepository;

    /**
     * @var DeleteRequestFactory
     */
    private $deleteRequestFactory;

    /**
     * @var ActionLogger
     */
    private $actionLogger;

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
     * @var CollectionFactory
     */
    private $deleteRequestCollectionFactory;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var GiftRegistryProvider
     */
    private $giftRegistryProvider;

    /**
     * @var NotificationsApplier
     */
    private $notificationsApplier;

    public function __construct(
        Context $context,
        Session $customerSession,
        LoggerInterface $logger,
        DeleteRequestFactory $deleteRequestFactory,
        DeleteRequestRepositoryInterface $deleteRequestRepository,
        ActionLogger $actionLogger,
        FormKeyValidator $formKeyValidator,
        Authentication $authentication,
        Config $configProvider,
        CollectionFactory $deleteRequestCollectionFactory,
        ProductMetadataInterface $productMetadata,
        GiftRegistryProvider $giftRegistryProvider,
        NotificationsApplier $notificationsApplier
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->deleteRequestRepository = $deleteRequestRepository;
        $this->deleteRequestFactory = $deleteRequestFactory;
        $this->actionLogger = $actionLogger;
        $this->formKeyValidator = $formKeyValidator;
        $this->authentication = $authentication;
        $this->configProvider = $configProvider;
        $this->deleteRequestCollectionFactory = $deleteRequestCollectionFactory;
        $this->productMetadata = $productMetadata;
        $this->giftRegistryProvider = $giftRegistryProvider;
        $this->notificationsApplier = $notificationsApplier;
    }

    public function execute()
    {
        $errorMessage = '';

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $errorMessage = __('Invalid Form Key. Please refresh the page.');
        }

        if (!$this->configProvider->isAllowed(Config::DELETE)) {
            $errorMessage = __('Access denied.');
        }

        if ($errorMessage) {
            $this->messageManager->addErrorMessage($errorMessage);

            return $this->resultRedirectFactory->create()->setPath('*/*/settings');
        }

        $customerId = (int)$this->customerSession->getCustomerId();
        $customerPass = $this->getRequest()->getParam('current_password');

        try {
            if ($customerId) {
                $this->authentication->authenticate($customerId, $customerPass);
            }
        } catch (\Magento\Framework\Exception\AuthenticationException $e) {
            $this->messageManager->addErrorMessage(__('Wrong Password. Please recheck it.'));

            return $this->resultRedirectFactory->create()->setPath('*/*/settings');
        }

        try {
            $deleteRequests = $this->deleteRequestCollectionFactory->create();

            if ($deleteRequests->addFieldToFilter('customer_id', $customerId)->getSize()) {
                $this->messageManager->addErrorMessage(
                    __('Your delete account request is awaiting for the review by the administrator.')
                );
            } elseif ($this->productMetadata->getEdition() === 'Enterprise'
                && $this->configProvider->isAvoidGiftRegistryAnonymization()
                && $this->giftRegistryProvider->checkGiftRegistries($customerId)
            ) {
                $this->messageManager->addErrorMessage(
                    __('We can not process your account deletion request right now, ' .
                        'because you have active Gift Registry.')
                );
            } else {
                /** @var DeleteRequest $request */
                $request = $this->deleteRequestFactory->create();
                $request->setCustomerId($customerId);
                $request->setGotFrom(DeleteRequestSource::CUSTOMER_REQUEST);
                $this->deleteRequestRepository->save($request);
                $this->actionLogger->logAction('delete_request_submitted', $request->getCustomerId());
                $this->notificationsApplier->apply(
                    NotifiersProvider::EVENT_DELETE_REQUEST_CREATED,
                    $customerId
                );
                $this->messageManager->addSuccessMessage(
                    __('Thank you, your account delete request was recorded.')
                );
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('An error has occurred'));
            $this->logger->critical($exception);
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/settings');
    }
}
