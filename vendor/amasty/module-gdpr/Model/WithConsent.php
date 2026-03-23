<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Api\Data\WithConsentExtensionInterface;
use Amasty\Gdpr\Api\Data\WithConsentInterface;
use Amasty\Gdpr\Model\ResourceModel\WithConsent as WithConsentResource;
use Magento\Framework\Model\AbstractExtensibleModel;

class WithConsent extends AbstractExtensibleModel implements WithConsentInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'amasty_gdpr_consent_log';

    public function _construct()
    {
        parent::_construct();

        $this->_init(WithConsentResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->_getData(WithConsentInterface::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($customerId)
    {
        $this->setData(WithConsentInterface::CUSTOMER_ID, $customerId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDateConsented()
    {
        return $this->_getData(WithConsentInterface::DATE_CONSENTED);
    }

    /**
     * @inheritdoc
     */
    public function setDateConsented($dateConsented)
    {
        $this->setData(WithConsentInterface::DATE_CONSENTED, $dateConsented);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPolicyVersion()
    {
        return $this->_getData(WithConsentInterface::POLICY_VERSION);
    }

    /**
     * @inheritdoc
     */
    public function setPolicyVersion($policyVersion)
    {
        $this->setData(WithConsentInterface::POLICY_VERSION, $policyVersion);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGotFrom()
    {
        return $this->_getData(WithConsentInterface::GOT_FROM);
    }

    /**
     * @inheritdoc
     */
    public function setGotFrom($from)
    {
        $this->setData(WithConsentInterface::GOT_FROM, $from);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWebsiteId()
    {
        return $this->_getData(WithConsentInterface::WEBSITE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setWebsiteId($websiteId)
    {
        $this->setData(WithConsentInterface::WEBSITE_ID, $websiteId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIp()
    {
        return $this->_getData(WithConsentInterface::IP);
    }

    /**
     * @inheritdoc
     */
    public function setIp($ip)
    {
        $this->setData(WithConsentInterface::IP, $ip);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAction()
    {
        return $this->_getData(WithConsentInterface::ACTION);
    }

    /**
     * @inheritdoc
     */
    public function setAction($action)
    {
        $this->setData(WithConsentInterface::ACTION, $action);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setConsentCode($consentCode)
    {
        $this->setData(WithConsentInterface::CONSENT_CODE, $consentCode);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getConsentCode()
    {
        return $this->_getData(WithConsentInterface::CONSENT_CODE);
    }

    public function setLoggedEmail($email)
    {
        $this->setData(WithConsentInterface::LOGGED_EMAIL, $email);

        return $this;
    }

    public function getLoggedEmail()
    {
        return $this->_getData(WithConsentInterface::LOGGED_EMAIL);
    }

    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    public function setExtensionAttributes(WithConsentExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
