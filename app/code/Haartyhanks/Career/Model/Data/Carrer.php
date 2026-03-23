<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Haartyhanks\Career\Model\Data;

use Haartyhanks\Career\Api\Data\CarrerInterface;

class Carrer extends \Magento\Framework\Api\AbstractExtensibleObject implements CarrerInterface
{

    /**
     * Get carrer_id
     * @return string|null
     */
    public function getCarrerId()
    {
        return $this->_get(self::CARRER_ID);
    }

    /**
     * Set carrer_id
     * @param string $carrerId
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setCarrerId($carrerId)
    {
        return $this->setData(self::CARRER_ID, $carrerId);
    }

    /**
     * Get first_name
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->_get(self::FIRST_NAME);
    }

    /**
     * Set first_name
     * @param string $firstName
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setFirstName($firstName)
    {
        return $this->setData(self::FIRST_NAME, $firstName);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Haartyhanks\Career\Api\Data\CarrerExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Haartyhanks\Career\Api\Data\CarrerExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Haartyhanks\Career\Api\Data\CarrerExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get last_name
     * @return string|null
     */
    public function getLastName()
    {
        return $this->_get(self::LAST_NAME);
    }

    /**
     * Set last_name
     * @param string $lastName
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setLastName($lastName)
    {
        return $this->setData(self::LAST_NAME, $lastName);
    }

    /**
     * Get phone
     * @return string|null
     */
    public function getPhone()
    {
        return $this->_get(self::PHONE);
    }

    /**
     * Set phone
     * @param string $phone
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setPhone($phone)
    {
        return $this->setData(self::PHONE, $phone);
    }

    /**
     * Get email
     * @return string|null
     */
    public function getEmail()
    {
        return $this->_get(self::EMAIL);
    }

    /**
     * Set email
     * @param string $email
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Get role
     * @return string|null
     */
    public function getRole()
    {
        return $this->_get(self::ROLE);
    }

    /**
     * Set role
     * @param string $role
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setRole($role)
    {
        return $this->setData(self::ROLE, $role);
    }

    /**
     * Get location
     * @return string|null
     */
    public function getLocation()
    {
        return $this->_get(self::LOCATION);
    }

    /**
     * Set location
     * @param string $location
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setLocation($location)
    {
        return $this->setData(self::LOCATION, $location);
    }

    /**
     * Get attachment
     * @return string|null
     */
    public function getAttachment()
    {
        return $this->_get(self::ATTACHMENT);
    }

    /**
     * Set attachment
     * @param string $attachment
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setAttachment($attachment)
    {
        return $this->setData(self::ATTACHMENT, $attachment);
    }

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}

