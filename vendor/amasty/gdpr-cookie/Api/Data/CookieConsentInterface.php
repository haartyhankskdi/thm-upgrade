<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Api\Data;

interface CookieConsentInterface
{
    public const ID = 'id';
    public const CUSTOMER_ID = 'customer_id';
    public const DATE_RECIEVED = 'date_recieved';
    public const CONSENT_STATUS = 'consent_status';
    public const WEBSITE = 'website';
    public const CUSTOMER_IP = 'customer_ip';
    public const GROUPS_STATUS = 'groups_status';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieConsentInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $id
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieConsentInterface
     */
    public function setCustomerId($id);

    /**
     * @return string
     */
    public function getDateRecieved();

    /**
     * @param string $date
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieConsentInterface
     */
    public function setDateRecieved($date);

    /**
     * @return string
     */
    public function getConsentStatus();

    /**
     * @param string $status
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieConsentInterface
     */
    public function setConsentStatus($status);

    /**
     * @return int
     */
    public function getWebsite();

    /**
     * @param int $websiteId
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieConsentInterface
     */
    public function setWebsite($websiteId);

    /**
     * @return string
     */
    public function getCustomerIp();

    /**
     * @param string $ip
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieConsentInterface
     */
    public function setCustomerIp($ip);

    /**
     * @return int
     */
    public function getGroupsStatus();

    /**
     * @param int $groupsStatus
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieConsentInterface
     */
    public function setGroupsStatus($groupsStatus);
}
