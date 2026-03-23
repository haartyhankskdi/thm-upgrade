<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Customer;

use Amasty\Gdpr\Model\ActionLogger;
use Amasty\Gdpr\Model\Anonymization\Anonymizer;
use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\GiftRegistryProvider;
use Amasty\Gdpr\Model\GuestOrderProvider;
use Magento\Customer\Model\Authentication;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\AuthenticationException;
use Psr\Log\LoggerInterface;

class Anonymise extends Action implements HttpPostActionInterface
{
    /**
     * @var Anonymizer
     */
    private $anonymizer;

    /**
     * @var \Magento\Customer\Model\Session
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
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var GiftRegistryProvider
     */
    private $giftRegistryProvider;

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
        Anonymizer $anonymizer,
        Session $customerSession,
        LoggerInterface $logger,
        FormKeyValidator $formKeyValidator,
        Authentication $authentication,
        Config $configProvider,
        ProductMetadataInterface $productMetadata,
        GiftRegistryProvider $giftRegistryProvider,
        GuestOrderProvider $guestOrderProvider,
        ActionLogger $actionLogger
    ) {
        parent::__construct($context);
        $this->anonymizer = $anonymizer;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->formKeyValidator = $formKeyValidator;
        $this->authentication = $authentication;
        $this->configProvider = $configProvider;
        $this->productMetadata = $productMetadata;
        $this->giftRegistryProvider = $giftRegistryProvider;
        $this->guestOrderProvider = $guestOrderProvider;
        $this->actionLogger = $actionLogger;
    }

    public function execute()
    {
        $errorMessage = '';

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $errorMessage = __('Invalid Form Key. Please refresh the page.');
        }

        if (!$this->configProvider->isAllowed(Config::ANONYMIZE)) {
            $errorMessage = __('Access denied.');
        }

        if ($errorMessage) {
            $this->messageManager->addErrorMessage($errorMessage);

            return $this->resultRedirectFactory->create()->setRefererUrl();
        }

        $incrementId = null;
        $customerId = (int)$this->customerSession->getCustomerId();

        try {
            if ($customerId) {
                $customerPass = $this->getRequest()->getParam('current_password');
                $this->authentication->authenticate($customerId, $customerPass);
            } else {
                $incrementId = $this->guestOrderProvider->getGuestOrder()->getIncrementId();
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

        try {
            $errorMessage = '';

            if ($customerId) {
                $ordersData = $this->anonymizer->getCustomerActiveOrders($customerId);
                if (!empty($ordersData)) {
                    $orderIncrementIds = '';

                    foreach ($ordersData as $order) {
                        $orderIncrementIds .= ' ' . $order['increment_id'];
                    }

                    $errorMessage = __(
                        'We can not anonymize your account right now, because you have non-completed order(s):%1',
                        $orderIncrementIds
                    );
                } elseif ($this->productMetadata->getEdition() === 'Enterprise'
                    && $this->configProvider->isAvoidGiftRegistryAnonymization()
                    && $this->giftRegistryProvider->checkGiftRegistries($customerId)
                ) {
                    $errorMessage = __(
                        'We can not anonymize your account right now, because you have active Gift Registry'
                    );
                } else {
                    $this->anonymizer->anonymizeCustomer($customerId);
                }
            } else {
                $result = $this->anonymizer->anonymizeOrder($incrementId);

                if (!$result) {
                    $errorMessage = __('We can not anonymize order');
                }
            }

            $action = sprintf(
                'data_%s_by_%s',
                $errorMessage ? 'anonymization_error' : 'anonymised',
                $customerId ? 'customer' : 'guest'
            );

            if ($errorMessage) {
                $this->messageManager->addErrorMessage($errorMessage);
                $this->actionLogger->logAction($action, $customerId, $errorMessage);
            } else {
                $this->messageManager->addSuccessMessage(__('Anonymization was successful'));

                if (!$customerId) {
                    $this->actionLogger->logAction($action);
                }
            }

        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('An error has occurred'));
            $this->logger->critical($exception);
        }

        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
