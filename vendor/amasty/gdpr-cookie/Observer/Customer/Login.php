<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Observer\Customer;

use Amasty\GdprCookie\Model\ConfigProvider;
use Amasty\GdprCookie\Model\CookieConsent\CookieGroupProcessor;
use Amasty\GdprCookie\Model\CookieConsentLogger;
use Amasty\GdprCookie\Model\CookieManager;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Login implements ObserverInterface
{
    /**
     * @var CookieManager
     */
    private $cookieManager;

    /**
     * @var CookieConsentLogger
     */
    private $consentLogger;

    /**
     * @var CookieGroupProcessor
     */
    private $cookieGroupProcessor;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        CookieManager $cookieManager,
        CookieConsentLogger $consentLogger,
        CookieGroupProcessor $cookieGroupProcessor,
        ?ConfigProvider $configProvider = null
    ) {
        $this->cookieManager = $cookieManager;
        $this->consentLogger = $consentLogger;
        $this->cookieGroupProcessor = $cookieGroupProcessor;
        $this->configProvider = $configProvider ?? ObjectManager::getInstance()->get(ConfigProvider::class);
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->configProvider->isCookieBarEnabled()
            || !($allowedCookieGroups = $this->cookieManager->getAllowCookies())
        ) {
            return;
        }
        $customer = $observer->getData('customer');
        $this->consentLogger->logCookieConsent(
            $customer ? (int)$customer->getData('entity_id') : 0,
            $this->cookieGroupProcessor->getAllowedGroupIds($allowedCookieGroups)
        );
    }
}
