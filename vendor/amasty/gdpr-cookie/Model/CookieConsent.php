<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model;

use Amasty\GdprCookie\Api\Data\CookieConsentInterface;
use Magento\Framework\Model\AbstractModel;

class CookieConsent extends AbstractModel implements CookieConsentInterface
{
    public const GROUPS_STATUS_ALL_ALLOWED = 1;
    public const GROUPS_STATUS_NONE_ALLOWED = 2;
    public const GROUPS_STATUS_SPECIFIC_GROUP = 3;

    private const ALLOWED_GROUP_IDS = 'allowed_group_ids';

    public function _construct()
    {
        $this->_init(ResourceModel\CookieConsent::class);
    }

    public function getId()
    {
        return $this->_getData(CookieConsent::ID);
    }

    public function setId($id)
    {
        $this->setData(CookieConsent::ID, $id);

        return $this;
    }

    public function getCustomerId()
    {
        return $this->_getData(CookieConsent::CUSTOMER_ID);
    }

    public function setCustomerId($id)
    {
        $this->setData(CookieConsent::CUSTOMER_ID, $id);

        return $this;
    }

    public function getDateRecieved()
    {
        return $this->_getData(CookieConsentInterface::DATE_RECIEVED);
    }

    public function setDateRecieved($date)
    {
        $this->setData(CookieConsentInterface::DATE_RECIEVED, $date);

        return $this;
    }

    public function getConsentStatus()
    {
        return $this->_getData(CookieConsentInterface::CONSENT_STATUS);
    }

    public function setConsentStatus($status)
    {
        $this->setData(CookieConsentInterface::CONSENT_STATUS, $status);

        return $this;
    }

    public function getWebsite()
    {
        return $this->_getData(CookieConsentInterface::WEBSITE);
    }

    public function setWebsite($websiteId)
    {
        $this->setData(CookieConsentInterface::WEBSITE, $websiteId);

        return $this;
    }

    public function getCustomerIp()
    {
        return $this->_getData(CookieConsentInterface::CUSTOMER_IP);
    }

    public function setCustomerIp($ip)
    {
        $this->setData(CookieConsentInterface::CUSTOMER_IP, $ip);

        return $this;
    }

    public function getGroupsStatus()
    {
        return $this->_getData(CookieConsentInterface::GROUPS_STATUS);
    }

    public function setGroupsStatus($groupsStatus)
    {
        $this->setData(CookieConsentInterface::GROUPS_STATUS, $groupsStatus);

        return $this;
    }

    public function getAllowedGroupIds(): ?array
    {
        return $this->_getData(self::ALLOWED_GROUP_IDS);
    }

    public function setAllowedGroupIds(?array $groupIds): self
    {
        $this->setData(self::ALLOWED_GROUP_IDS, $groupIds);

        return $this;
    }
}
