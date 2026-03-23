<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Customer;

use Amasty\Gdpr\Model\ConsentEmail\CustomerKeyGenerator;
use Amasty\Gdpr\Model\ConsentLogger;
use Amasty\Gdpr\Model\ConsentVisitorLogger;
use Amasty\Gdpr\Model\Source\ConsentLinkType;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Psr\Log\LoggerInterface;

class Login extends AbstractAccount
{
    /**
     * @var CustomerKeyGenerator
     */
    private $customerKeyGenerator;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConsentLogger
     */
    private $consentLogger;

    /**
     * @var ConsentVisitorLogger
     */
    private $consentVisitorLogger;

    public function __construct(
        Context $context,
        CustomerKeyGenerator $customerKeyGenerator,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        ConsentLogger $consentLogger,
        ConsentVisitorLogger $consentVisitorLogger
    ) {
        parent::__construct($context);
        $this->customerKeyGenerator = $customerKeyGenerator;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->consentLogger = $consentLogger;
        $this->consentVisitorLogger = $consentVisitorLogger;
    }

    public function execute()
    {
        try {
            $params = $this->getRequest()->getParams();
            $customerId = (int)($params['customer_id'] ?? 0);
            $policyVersion = (string)($params['policy_version'] ?? '');
            $requestKey = $params['key'] ?? null;
            $generatedKey = $this->customerKeyGenerator->generateKey($customerId, $policyVersion);
            if ($requestKey == $generatedKey) {
                $customerIsLoggedIn = $this->customerSession->isLoggedIn();
                if ($customerIsLoggedIn && ($customerId != $this->customerSession->getCustomerId())) {
                    $this->customerSession->logout();
                    $customerIsLoggedIn = false;
                }

                if (!$customerIsLoggedIn) {
                    $customer = $this->customerRepository->getById($customerId);
                    if ($customer->getId()) {
                        $this->customerSession->setCustomerDataAsLoggedIn($customer);
                    }
                }

                $this->consentVisitorLogger->log(
                    $policyVersion,
                    $customerId,
                    (string)$this->customerSession->getSessionId()
                );
                $this->consentLogger->logParams(
                    $customerId,
                    ConsentLogger::FROM_EMAIL,
                    ConsentLinkType::PRIVACY_POLICY,
                    true,
                    null,
                    $this->customerSession->getCustomer()->getEmail()
                );
                $this->messageManager->addSuccessMessage(
                    __('Thank you for your cooperation. Your consent was recorded.')
                );
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong.')
            );
            $this->logger->critical($exception);
        }

        return $this->resultRedirectFactory->create()->setPath('customer/account');
    }
}
