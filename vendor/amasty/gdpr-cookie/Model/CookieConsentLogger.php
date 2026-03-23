<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model;

use Amasty\Base\Model\GetCustomerIp;
use Amasty\GdprCookie\Api\CookieConsentRepositoryInterface;
use Amasty\GdprCookie\Model\CookieConsent\CookieGroupProcessor;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;

class CookieConsentLogger
{
    /**
     * @var CookieConsentRepositoryInterface
     */
    private $cookieConsentRepository;

    /**
     * @var CookieConsentFactory
     */
    private $cookieConsentFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var GetCustomerIp
     */
    private $customerIp;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CookieGroupProcessor
     */
    private $cookieGroupProcessor;

    public function __construct(
        CookieConsentRepositoryInterface $cookieConsentRepository,
        CookieConsentFactory $cookieConsentFactory,
        GetCustomerIp $customerIp,
        StoreManagerInterface $storeManager,
        DateTime $date,
        ConfigProvider $configProvider,
        CookieGroupProcessor $cookieGroupProcessor
    ) {
        $this->cookieConsentRepository = $cookieConsentRepository;
        $this->cookieConsentFactory = $cookieConsentFactory;
        $this->storeManager = $storeManager;
        $this->date = $date;
        $this->customerIp = $customerIp;
        $this->configProvider = $configProvider;
        $this->cookieGroupProcessor = $cookieGroupProcessor;
    }

    public function logCookieConsent(?int $customerId, ?array $allowedCookieGroupIds = null)
    {
        if (!$this->configProvider->isCookieBarEnabled()
            || (!$customerId && !$this->configProvider->isLogGuest())
        ) {
            return;
        }

        $cookieConsent = $this->cookieConsentRepository->getByCustomerId($customerId);
        $website = $this->storeManager->getWebsite()->getId();
        $customerIp = $this->getRemoteIp();
        $groupsStatus = $this->cookieGroupProcessor->getGroupsStatus($allowedCookieGroupIds);

        if ($cookieConsent && $customerId) {
            $cookieConsent->setCustomerId($customerId)
                ->setConsentStatus('') // ToDo: Delete row after removing "ConsentStatus" column
                ->setWebsite($website)
                ->setDateRecieved($this->date->gmtDate())
                ->setCustomerIp($customerIp)
                ->setGroupsStatus($groupsStatus)
                ->setAllowedGroupIds($allowedCookieGroupIds);
            $this->cookieConsentRepository->save($cookieConsent);

            return;
        }

        $consent = $this->cookieConsentFactory->create();
        $consent->setCustomerId($customerId)
            ->setConsentStatus('') // ToDo: Delete row after removing "ConsentStatus" column
            ->setWebsite($this->storeManager->getWebsite()->getId())
            ->setCustomerIp($this->getRemoteIp())
            ->setGroupsStatus($groupsStatus)
            ->setAllowedGroupIds($allowedCookieGroupIds);
        $this->cookieConsentRepository->save($consent);
    }

    public function getRemoteIp()
    {
        $ip = $this->customerIp->getCurrentIp();
        $isIpv6 = (bool)filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        if ($isIpv6) {
            $ip = $this->getPreparedIp($ip, ':');
        } else {
            $ip = $this->getPreparedIp($ip, '.');
        }

        return $ip;
    }

    private function getPreparedIp(string $ip, string $addressSeparator): string
    {
        $addressParts = explode($addressSeparator, $ip);
        array_pop($addressParts);
        $addressParts[] = '0'; // Mask IP according to EU GDPR law

        return implode($addressSeparator, $addressParts);
    }
}
