<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Notification\Notifier;

use Amasty\Gdpr\Api\Data\PolicyInterface;
use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\ConsentEmail\CustomerKeyGenerator;
use Amasty\Gdpr\Model\Utils\EmailSender;
use Amasty\Gdpr\Model\VisitorConsentLog\ResourceModel\VisitorConsentLog as VisitorConsentLogResource;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

class PolicyChangeNotifier extends AbstractNotifier
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    /**
     * @var VisitorConsentLogResource
     */
    private $visitorConsentLogResource;

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var CustomerKeyGenerator
     */
    private $customerKeyGenerator;

    /**
     * @var array
     */
    private $policyTexts = [];

    public function __construct(
        EmailSender $emailSender,
        Config $configProvider,
        SenderResolverInterface $senderResolver,
        CustomerNameGenerationInterface $nameGeneration,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        PolicyRepositoryInterface $policyRepository,
        VisitorConsentLogResource $visitorConsentLogResource,
        FilterProvider $filterProvider,
        CustomerKeyGenerator $customerKeyGenerator
    ) {
        parent::__construct(
            $emailSender,
            $configProvider,
            $senderResolver,
            $nameGeneration,
            $customerRepository
        );
        $this->storeManager = $storeManager;
        $this->policyRepository = $policyRepository;
        $this->visitorConsentLogResource = $visitorConsentLogResource;
        $this->filterProvider = $filterProvider;
        $this->customerKeyGenerator = $customerKeyGenerator;
    }

    public function notify(
        int $customerId,
        array $vars = []
    ): bool {
        $customer = $this->getCustomer($customerId);
        if (!$customer || !$this->configProvider->isPolicyChangeNotificationEnabled($customer->getStoreId())) {
            return true;
        }

        $policy = $this->policyRepository->getCurrentPolicy($customer->getStoreId());
        if (!$policy) {
            return true;
        }

        $customerPolicyVersion = $this->visitorConsentLogResource->getCustomerPolicyVersion(
            $customerId,
            null,
            (int)$this->storeManager->getStore($customer->getStoreId())->getWebsiteId()
        );

        if ($customerPolicyVersion && $customerPolicyVersion == $policy->getPolicyVersion()) {
            return true;
        }

        $customerName = $this->nameGeneration->getCustomerName($customer);
        $sender = $this->configProvider->getPolicyChangeEmailSender($customer->getStoreId());
        $replyTo = $this->configProvider->getPolicyChangeEmailReplyTo($customer->getStoreId());
        if (!trim($replyTo)) {
            $result = $this->senderResolver->resolve($sender);
            $replyTo = $result['email'];
        }

        $vars += [
            'customer_name' => $customerName,
            'account_url' => $this->getAccountUrl(
                (int)$customer->getId(),
                (int)$customer->getStoreId(),
                (string)$policy->getPolicyVersion()
            ),
            'policy_text' => $this->getPolicyText($policy)
        ];

        return $this->emailSender->sendEmail(
            [[$customer->getEmail(), $customerName]],
            $sender,
            (int)$customer->getStoreId(),
            $this->configProvider->getPolicyChangeEmailTemplate($customer->getStoreId()),
            $vars,
            $replyTo
        );
    }

    private function getPolicyText(PolicyInterface $policy): string
    {
        if (isset($this->policyTexts[$policy->getPolicyVersion()])) {
            return $this->policyTexts[$policy->getPolicyVersion()];
        }

        return $this->policyTexts[$policy->getPolicyVersion()] = $this->filterProvider->getPageFilter()
            ->filter($policy->getContent());
    }

    private function getAccountUrl(int $customerId, int $storeId, string $policyVersion): string
    {
        return $this->storeManager->getStore($storeId)->getUrl(
            'gdpr/customer/login',
            [
                'customer_id' => $customerId,
                'policy_version' => $policyVersion,
                'key' => $this->customerKeyGenerator->generateKey($customerId, $policyVersion)
            ]
        );
    }
}
