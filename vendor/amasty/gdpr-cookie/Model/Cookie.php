<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model;

use Amasty\GdprCookie\Api\Data\CookieInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Cookie extends AbstractModel implements CookieInterface, IdentityInterface
{
    public const CACHE_TAG = 'amasty_cookie';
    public const SENSITIVE_FIELDS = [
        CookieInterface::GROUP_ID,
        CookieInterface::NAME,
        CookieInterface::DESCRIPTION,
        CookieInterface::IS_ENABLED,
        CookieInterface::LIFETIME,
        CookieInterface::PROVIDER,
        CookieInterface::TYPE,
    ];

    public function _construct()
    {
        $this->_init(ResourceModel\Cookie::class);
    }

    /**
     * @inheritdoc
     */
    public function getGroupId()
    {
        return $this->_getData(CookieInterface::GROUP_ID);
    }

    /**
     * @inheritdoc
     */
    public function setGroupId($groupId)
    {
        $this->setData(CookieInterface::GROUP_ID, $groupId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->_getData(CookieInterface::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData(CookieInterface::NAME, $name);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->_getData(CookieInterface::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        $this->setData(CookieInterface::DESCRIPTION, $description);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return (bool)$this->_getData(CookieInterface::IS_ENABLED);
    }

    /**
     * @inheritdoc
     */
    public function setIsEnabled($isEnabled)
    {
        $this->setData(CookieInterface::IS_ENABLED, $isEnabled);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLifetime()
    {
        return $this->_getData(CookieInterface::LIFETIME);
    }

    /**
     * @inheritdoc
     */
    public function setLifetime($lifetime)
    {
        $this->setData(CookieInterface::LIFETIME, $lifetime);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProvider()
    {
        return $this->_getData(CookieInterface::PROVIDER);
    }

    /**
     * @inheritdoc
     */
    public function setProvider($provider)
    {
        $this->setData(CookieInterface::PROVIDER, $provider);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->_getData(CookieInterface::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->setData(CookieInterface::TYPE, $type);

        return $this;
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG];
    }

    /**
     * Get list of cache tags applied to model object.
     *
     * @return array
     */
    public function getCacheTags()
    {
        $tags = parent::getCacheTags();
        if (!$tags) {
            $tags = [];
        }
        return $tags + $this->getIdentities();
    }

    public function getSensitiveData(): array
    {
        return array_intersect_key($this->getData(), array_flip(self::SENSITIVE_FIELDS));
    }
}
