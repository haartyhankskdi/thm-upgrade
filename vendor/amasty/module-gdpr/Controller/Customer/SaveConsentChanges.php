<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Customer;

use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\Consent\RegistryConstants;
use Magento\Customer\Controller\AbstractAccount as AbstractAccountAction;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class SaveConsentChanges extends AbstractAccountAction
{
    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Context $context,
        FormKeyValidator $formKeyValidator,
        Config $configProvider,
        ManagerInterface $eventManager,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->configProvider = $configProvider;
        $this->eventManager = $eventManager;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $errorMessage = '';

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $errorMessage = __('Invalid Form Key. Please refresh the page.');
        }

        if (!$this->configProvider->isAllowed(Config::CONSENT_OPTING)) {
            $errorMessage = __('Access denied.');
        }

        if ($errorMessage) {
            $this->messageManager->addErrorMessage($errorMessage);

            return $this->resultRedirectFactory->create()->setPath('*/*/settings');
        }

        try {
            $consents = $this->getRequest()->getParam(RegistryConstants::CONSENTS, []);
            $from = $this->getRequest()->getParam(RegistryConstants::CONSENT_FROM);
            $customerId = $this->customerSession->getCustomerId();
            $storeId = (int)$this->storeManager->getStore()->getId();

            $this->eventManager->dispatch(
                'amasty_gdpr_consent_accept',
                [
                    RegistryConstants::CONSENTS => $consents,
                    RegistryConstants::CONSENT_FROM => $from,
                    RegistryConstants::CUSTOMER_ID => $customerId,
                    RegistryConstants::STORE_ID => $storeId
                ]
            );
            $this->messageManager->addSuccessMessage(__('Changes has been saved.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error has occurred'));
            $this->logger->critical($e);
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/settings');
    }
}
