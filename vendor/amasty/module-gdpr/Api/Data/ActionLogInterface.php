<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Api\Data;

interface ActionLogInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const ID = 'id';
    public const CUSTOMER_ID = 'customer_id';
    public const CREATED_AT = 'created_at';
    public const IP = 'ip';
    public const ACTION = 'action';
    public const COMMENT = 'comment';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\Gdpr\Api\Data\ActionLogInterface
     */
    public function setId($id);

    /**
     * @return int|null
     */
    public function getCustomerId();

    /**
     * @param int|null $customerId
     *
     * @return \Amasty\Gdpr\Api\Data\ActionLogInterface
     */
    public function setCustomerId($customerId);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return \Amasty\Gdpr\Api\Data\ActionLogInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getIp();

    /**
     * @param string $ip
     *
     * @return \Amasty\Gdpr\Api\Data\ActionLogInterface
     */
    public function setIp($ip);

    /**
     * @return string
     */
    public function getAction();

    /**
     * @param string $action
     *
     * @return \Amasty\Gdpr\Api\Data\ActionLogInterface
     */
    public function setAction($action);

    /**
     * @return string
     */
    public function getComment(): string;

    /**
     * @param string $comment
     *
     * @return \Amasty\Gdpr\Api\Data\ActionLogInterface
     */
    public function setComment(string $comment);
}
