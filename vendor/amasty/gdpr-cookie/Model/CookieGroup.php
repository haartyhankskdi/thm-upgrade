<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model;

use Amasty\GdprCookie\Api\Data\CookieGroupsInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class CookieGroup extends AbstractModel implements CookieGroupsInterface, IdentityInterface
{
    public const CACHE_TAG = 'amasty_cookie_groups';
    public const SENSITIVE_FIELDS = [
        CookieGroupsInterface::NAME,
        CookieGroupsInterface::DESCRIPTION,
        CookieGroupsInterface::IS_ESSENTIAL,
        CookieGroupsInterface::IS_ENABLED,
        'cookies'
    ];

    public function _construct()
    {
        $this->_init(ResourceModel\CookieGroup::class);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->_getData(CookieGroupsInterface::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData(CookieGroupsInterface::NAME, $name);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->_getData(CookieGroupsInterface::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        $this->setData(CookieGroupsInterface::DESCRIPTION, $description);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isEssential()
    {
        return (bool)$this->_getData(CookieGroupsInterface::IS_ESSENTIAL);
    }

    /**
     * @inheritdoc
     */
    public function setIsEssential($isEssential)
    {
        $this->setData(CookieGroupsInterface::IS_ESSENTIAL, $isEssential);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return (bool)$this->_getData(CookieGroupsInterface::IS_ENABLED);
    }

    /**
     * @inheritdoc
     */
    public function setIsEnabled($isEnabled)
    {
        $this->setData(CookieGroupsInterface::IS_ENABLED, $isEnabled);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSortOrder()
    {
        return (int)$this->_getData(CookieGroupsInterface::SORT_ORDER);
    }

    /**
     * @inheritdoc
     */
    public function setSortOrder($sortOrder)
    {
        $this->setData(CookieGroupsInterface::SORT_ORDER, $sortOrder);

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
