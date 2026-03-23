<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Policy;

use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\Consent\DataProvider\ConsentPolicyContentResolver;
use Amasty\Gdpr\Model\Policy;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\PageCache\Model\Config as PageCacheConfig;

class PolicyText extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var PageCacheConfig
     */
    private $pageCacheConfig;

    /**
     * @var ConsentPolicyContentResolver
     */
    private $policyContentResolver;

    public function __construct(
        Context $context,
        Config $configProvider,
        FilterProvider $filterProvider,
        PageCacheConfig $pageCacheConfig,
        ConsentPolicyContentResolver $policyContentResolver
    ) {
        parent::__construct($context);
        $this->configProvider = $configProvider;
        $this->filterProvider = $filterProvider;
        $this->pageCacheConfig = $pageCacheConfig;
        $this->policyContentResolver = $policyContentResolver;
    }

    public function execute()
    {
        $policyData = [];
        $consentId = (int)$this->getRequest()->getParam('consent_id');
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $this->getResponse()->setPublicHeaders($this->pageCacheConfig->getTtl());
        $resultJson->setHeader('X-Magento-Tags', Policy::CACHE_TAG);

        if ($this->configProvider->isModuleEnabled()) {
            $policyData = $consentId
                ? $this->policyContentResolver->getConsentPolicyData($consentId)
                : $this->policyContentResolver->getGeneralPolicyData();

            if (isset($policyData[ConsentPolicyContentResolver::DATA_CONTENT])) {
                $policyData[ConsentPolicyContentResolver::DATA_CONTENT] = $this->filterProvider->getPageFilter()
                    ->filter((string)$policyData[ConsentPolicyContentResolver::DATA_CONTENT]);
            }
        }

        return $resultJson->setData($policyData);
    }
}
