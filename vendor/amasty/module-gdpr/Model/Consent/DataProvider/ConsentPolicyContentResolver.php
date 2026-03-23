<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Consent\DataProvider;

use Amasty\Gdpr\Api\ConsentRepositoryInterface;
use Amasty\Gdpr\Api\Data\ConsentInterface;
use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStore;
use Amasty\Gdpr\Model\Source\ConsentLinkType;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConsentPolicyContentResolver
{
    public const DATA_TITLE = 'title';
    public const DATA_CONTENT = 'content';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var ConsentRepositoryInterface
     */
    private $consentRepository;

    public function __construct(
        StoreManagerInterface $storeManager,
        PageRepositoryInterface $pageRepository,
        PolicyRepositoryInterface $policyRepository,
        ConsentRepositoryInterface $consentRepository
    ) {
        $this->storeManager = $storeManager;
        $this->pageRepository = $pageRepository;
        $this->policyRepository = $policyRepository;
        $this->consentRepository = $consentRepository;
    }

    public function getConsentPolicyData(int $consentId): array
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $consent = $this->consentRepository->getById($consentId, $storeId);
        $privacyLinkType = $consent->getPrivacyLinkType() ?: ConsentLinkType::PRIVACY_POLICY;

        switch ($privacyLinkType) {
            case ConsentLinkType::CMS_PAGE:
                return $this->getCmsPagePolicyData($consent);
            default:
                return $this->getGeneralPolicyData();
        }
    }

    public function getGeneralPolicyData(): array
    {
        $policy = $this->policyRepository->getCurrentPolicy(
            $this->storeManager->getStore()->getId()
        );

        return [
            self::DATA_TITLE => __('Privacy Policy'),
            self::DATA_CONTENT => $policy ? $policy->getContent() : ''
        ];
    }

    private function getCmsPagePolicyData(ConsentInterface $consent): array
    {
        if ($cmsPageId = (int)$consent->getStoreModel()->getData(ConsentStore::CMS_PAGE_ID)) {
            try {
                $cmsPage = $this->pageRepository->getById($cmsPageId);

                return [
                    self::DATA_TITLE => $cmsPage->getTitle() ?: __('Privacy Policy'),
                    self::DATA_CONTENT => $cmsPage->getContent() ?: ''
                ];
            } catch (\Exception $e) {
                null;
            }
        }

        return [];
    }
}
