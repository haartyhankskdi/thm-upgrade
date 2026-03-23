<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Policy\DataProvider;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\Policy\Validator\Displaying;
use Amasty\Gdpr\Model\VisitorConsentLog\ResourceModel\VisitorConsentLog as VisitorConsentLogResource;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\FormKey;
use Magento\Store\Model\StoreManagerInterface;

class PolicyPopupDataProvider
{
    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var VisitorConsentLogResource
     */
    private $visitorConsentLogResource;

    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Displaying
     */
    private $policyDisplaying;

    public function __construct(
        Config $configProvider,
        FormKey $formKey,
        Session $customerSession,
        VisitorConsentLogResource $visitorConsentLogResource,
        PolicyRepositoryInterface $policyRepository,
        StoreManagerInterface $storeManager,
        ?Displaying $policyDisplaying = null
    ) {
        $this->configProvider = $configProvider;
        $this->formKey = $formKey;
        $this->customerSession = $customerSession;
        $this->visitorConsentLogResource = $visitorConsentLogResource;
        $this->policyRepository = $policyRepository;
        $this->storeManager = $storeManager;
        // OM for backward compatibility
        $this->policyDisplaying = $policyDisplaying ?? ObjectManager::getInstance()->get(Displaying::class);
    }

    public function getData(): array
    {
        $result = [
            'policyVersion' => null,
            'show' => false,
            'title' => __('Privacy Policy'),
            'versionChanged' => false,
            'hideClose' => false,
            'action' => true
        ];

        if (!$this->policyDisplaying->isDisplay()) {
            return $result;
        }
        $storeId = (int)$this->storeManager->getStore()->getId();
        $websiteId = (int)$this->storeManager->getStore()->getWebsiteId();
        $customerId = (int)$this->customerSession->getCustomerId();
        $sessionId = (string)$this->customerSession->getSessionId();
        $customerPolicyVersion = $this->visitorConsentLogResource->getCustomerPolicyVersion(
            $customerId,
            $sessionId,
            $websiteId
        );
        $policy = $this->policyRepository->getCurrentPolicy($storeId);
        $result['policyVersion'] = $policy ? $policy->getPolicyVersion() : '';
        if (!empty($customerPolicyVersion)) {
            if ($result['policyVersion'] != $customerPolicyVersion) {
                $result['show'] = true;
                $result['versionChanged'] = true;
            }
        } else {
            $result['show'] = true;
        }

        return $result;
    }
}
