<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Haartyhanks\Career\Api\Data;

interface CarrerInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const UPDATED_AT = 'updated_at';
    const ATTACHMENT = 'attachment';
    const FIRST_NAME = 'first_name';
    const CREATED_AT = 'created_at';
    const LAST_NAME = 'last_name';
    const LOCATION = 'location';
    const ROLE = 'role';
    const EMAIL = 'email';
    const PHONE = 'phone';
    const CARRER_ID = 'carrer_id';

    /**
     * Get carrer_id
     * @return string|null
     */
    public function getCarrerId();

    /**
     * Set carrer_id
     * @param string $carrerId
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setCarrerId($carrerId);

    /**
     * Get first_name
     * @return string|null
     */
    public function getFirstName();

    /**
     * Set first_name
     * @param string $firstName
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setFirstName($firstName);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Haartyhanks\Career\Api\Data\CarrerExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Haartyhanks\Career\Api\Data\CarrerExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Haartyhanks\Career\Api\Data\CarrerExtensionInterface $extensionAttributes
    );

    /**
     * Get last_name
     * @return string|null
     */
    public function getLastName();

    /**
     * Set last_name
     * @param string $lastName
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setLastName($lastName);

    /**
     * Get phone
     * @return string|null
     */
    public function getPhone();

    /**
     * Set phone
     * @param string $phone
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setPhone($phone);

    /**
     * Get email
     * @return string|null
     */
    public function getEmail();

    /**
     * Set email
     * @param string $email
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setEmail($email);

    /**
     * Get role
     * @return string|null
     */
    public function getRole();

    /**
     * Set role
     * @param string $role
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setRole($role);

    /**
     * Get location
     * @return string|null
     */
    public function getLocation();

    /**
     * Set location
     * @param string $location
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setLocation($location);

    /**
     * Get attachment
     * @return string|null
     */
    public function getAttachment();

    /**
     * Set attachment
     * @param string $attachment
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setAttachment($attachment);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     */
    public function setUpdatedAt($updatedAt);
}

