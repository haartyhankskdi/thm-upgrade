<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Policy;

use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\ConsentLogger;
use Amasty\Gdpr\Model\ConsentProvider;
use Amasty\Gdpr\Model\ConsentVisitorLogger;
use Amasty\Gdpr\Model\Policy\Validator\Displaying;
use Amasty\Gdpr\Model\Source\ConsentLinkType;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Accept extends Action
{
    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ConsentProvider
     */
    private $consentProvider;

    /**
     * @var ConsentVisitorLogger
     */
    private $consentVisitorLogger;

    /**
     * @var ConsentLogger
     */
    private $consentLogger;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Displaying
     */
    private $policyDisplaying;

    public function __construct(
        Context $context,
        Config $configProvider,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        ConsentProvider $consentProvider,
        ConsentVisitorLogger $consentVisitorLogger,
        ConsentLogger $consentLogger,
        FormKeyValidator $formKeyValidator,
        LoggerInterface $logger,
        ?Displaying $policyDisplaying = null
    ) {
        parent::__construct($context);
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->consentProvider = $consentProvider;
        $this->consentVisitorLogger = $consentVisitorLogger;
        $this->consentLogger = $consentLogger;
        $this->formKeyValidator = $formKeyValidator;
        $this->logger = $logger;
        // OM for backward compatibility
        $this->policyDisplaying = $policyDisplaying ?? ObjectManager::getInstance()->get(Displaying::class);
    }

    private function isRequestValid(&$message): bool
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $message = __('Invalid Form Key. Please refresh the page.');
        } elseif (!$this->getRequest()->getParam('policyVersion')) {
            $message = __('Invalid Policy version.');
        } elseif ($this->getRequest()->getParam('action') === null) {
            $message = __('Invalid Policy action.');
        }

        return empty($message);
    }

    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $error = true;
        $message = '';
        $policyVersion = (string)$this->getRequest()->getParam('policyVersion');
        $action = $this->getRequest()->getParam('action');

        if ($this->policyDisplaying->isDisplay()
            && $this->isRequestValid($message)
        ) {
            try {
                $email = null;
                $customerId = (int)$this->customerSession->getCustomerId();
                $sessionId = (string)$this->customerSession->getSessionId();
                $storeId = (int)$this->storeManager->getStore()->getId();
                if ($customerData = $this->customerSession->getCustomerData()) {
                    $email = $customerData->getEmail();
                }
                $this->consentVisitorLogger->log($policyVersion, $customerId, $sessionId);
                $this->consentLogger->logParams(
                    $customerId,
                    ConsentLogger::PRIVACY_POLICY_POPUP,
                    ConsentLinkType::PRIVACY_POLICY,
                    (bool)$action,
                    null,
                    $email
                );

                /** @var \Amasty\Gdpr\Api\Data\ConsentInterface|\Amasty\Gdpr\Model\Consent\Consent $consent */
                foreach ($this->consentProvider->getConsentsByStore($storeId) as $consent) {
                    $this->consentLogger->logParams(
                        $customerId,
                        ConsentLogger::PRIVACY_POLICY_POPUP,
                        $consent->getPrivacyLinkType(),
                        (bool)$action,
                        $consent->getConsentCode(),
                        $email
                    );
                }
                $message = __('Privacy policy accepted.');
                $error = false;
            } catch (\Exception $e) {
                $message = __('An error has occurred');
                $this->logger->critical($e);
            }
        }

        return $resultJson->setData(compact('error', 'message'));
    }
}
