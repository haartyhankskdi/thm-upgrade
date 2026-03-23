<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Api\WithConsentRepositoryInterface;
use Amasty\Gdpr\Model\Source\ConsentLinkType;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class ConsentLogger
{
    public const EMPTY_EMAIL_PLACEHOLDER = '-';
    public const EMPTY_CONSENT_CODE_PLACEHOLDER = '-';

    public const FROM_REGISTRATION = 'registration';
    public const FROM_CHECKOUT = 'checkout';
    public const FROM_SUBSCRIPTION = 'subscription';
    public const FROM_CONTACTUS = 'contactus';
    public const FROM_EMAIL = 'email';
    public const FROM_PRIVACY_SETTINGS = 'privacy_settings';
    public const PRIVACY_POLICY_POPUP = 'privacy_policy_popup';

    public const CMS_PAGE = 'CMS Page';

    /**
     * @var WithConsentRepositoryInterface
     */
    private $withConsentRepository;

    /**
     * @var WithConsentFactory
     */
    private $consentFactory;

    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Gdpr\Model\Visitor
     */
    private $visitor;

    /**
     * @var Config
     */
    private $configProvider;

    public function __construct(
        WithConsentRepositoryInterface $withConsentRepository,
        WithConsentFactory $consentFactory,
        PolicyRepositoryInterface $policyRepository,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        Visitor $visitor,
        Config $configProvider
    ) {
        $this->withConsentRepository = $withConsentRepository;
        $this->consentFactory = $consentFactory;
        $this->policyRepository = $policyRepository;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->visitor = $visitor;
        $this->configProvider = $configProvider;
    }

    /**
     * @param string|int $customerId
     * @param string $from
     * @param Consent\Consent $consentModel
     * @param string|null $email
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function log($customerId, $from, $consentModel, ?string $email = null)
    {
        try {
            $this->logParams(
                $customerId,
                $from,
                $consentModel->getPrivacyLinkType(),
                (bool)$consentModel->isConsentAccepted(),
                (string)$consentModel->getConsentCode(),
                $email
            );
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
    }

    /**
     * @param $customerId
     * @param $from
     * @param null $linkType
     * @param bool $action
     * @param string|null $consentCode
     * @param string|null $email
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function logParams(
        $customerId,
        $from,
        $linkType = null,
        bool $action = false,
        ?string $consentCode = null,
        ?string $email = null
    ) {
        if (!$customerId && !$this->configProvider->isLogGuest($this->storeManager->getStore()->getWebsiteId())) {
            return;
        }

        if ($policy = $this->policyRepository->getCurrentPolicy()) {
            try {
                /** @var WithConsent $withConsent */
                $withConsent = $this->consentFactory->create();
                $privacyPolicyVersionValue = $linkType === ConsentLinkType::PRIVACY_POLICY ?
                    $policy->getPolicyVersion() : self::CMS_PAGE;
                $withConsent->setPolicyVersion($privacyPolicyVersionValue);
                $withConsent->setGotFrom($from);
                $withConsent->setWebsiteId($this->storeManager->getWebsite()->getId());
                $withConsent->setIp($this->visitor->getRemoteIp());
                $withConsent->setCustomerId($customerId);
                $withConsent->setAction($action);
                $withConsent->setConsentCode($consentCode ?? self::EMPTY_CONSENT_CODE_PLACEHOLDER);
                $withConsent->setLoggedEmail($email ?? self::EMPTY_EMAIL_PLACEHOLDER);

                $this->withConsentRepository->save($withConsent);
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
            }
        }
    }
}
