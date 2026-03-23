<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Controller\Cookie;

use Amasty\GdprCookie\Model\Cookie;
use Amasty\GdprCookie\Model\Cookie\CookieData;
use Amasty\GdprCookie\Model\CookieGroup;
use Amasty\GdprCookie\Model\CookiePolicy;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\PageCache\Model\Config;
use Magento\Store\Model\StoreManagerInterface;

class Cookies extends Action
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $pageCacheConfig;

    /**
     * @var CookieData
     */
    private $cookieData;

    /**
     * @var CookiePolicy
     */
    private $cookiePolicy;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Config $pageCacheConfig,
        CookieData $cookieData,
        CookiePolicy $cookiePolicy
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->pageCacheConfig = $pageCacheConfig;
        $this->cookieData = $cookieData;
        $this->cookiePolicy = $cookiePolicy;
    }

    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        /** @var ResponseInterface $response */
        $response = $this->getResponse();
        $resultJson->setHeader(
            'X-Magento-Tags',
            implode(
                ',',
                [
                    CookieGroup::CACHE_TAG,
                    Cookie::CACHE_TAG
                ]
            )
        );
        /**
         * We MUST set response TTL to zero to prevent response without restriction parameter caching via
         * FullPage Cache or Varnish to check customers' locations and pass 'allowed' or 'denied' to frontend.
         *
         * In case of 'Countries Restrictions' setting change we MUST validate current client's restriction
         * until full cache synchronisation with url parameters.
         *
         * Example: The client was restricted and passed restriction="denied" GET parameter, but response cache was
         * invalidated and 'Countries Restrictions' setting was changed to 'All Countries', so to prevent caching
         * 'allowed' cookiePolicy response with restriction="denied" parameter we MUST set TTL to zero. The next
         * client's query will provide right restriction="allowed" parameter, so we can cache the response.
         */
        $clientRestriction = $this->getRequest()->getParam('restriction', '');
        $policyRestriction = $this->cookiePolicy->isCookiePolicyAllowed() ? 'allowed' : 'denied';
        $ttl = $policyRestriction === $clientRestriction
            ? $this->pageCacheConfig->getTtl()
            : 0;
        $response->setPublicHeaders($ttl);
        $storeId = (int)$this->storeManager->getStore()->getId();
        $resultData = $this->cookieData->getGroupData($storeId);
        $resultData['cookiePolicy'] = $policyRestriction;
        $resultJson->setData($resultData);

        return $resultJson;
    }
}
