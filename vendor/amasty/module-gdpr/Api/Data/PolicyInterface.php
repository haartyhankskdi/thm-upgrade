<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Api\Data;

interface PolicyInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const ID = 'id';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const POLICY_VERSION = 'policy_version';
    public const CONTENT = 'content';
    public const LAST_EDITED_BY = 'last_edited_by';
    public const COMMENT = 'comment';
    public const STATUS = 'status';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     *
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return string
     */
    public function getPolicyVersion();

    /**
     * @param string $policyVersion
     *
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface
     */
    public function setPolicyVersion($policyVersion);

    /**
     * @return string
     */
    public function getContent();

    /**
     * @param string $content
     *
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface
     */
    public function setContent($content);

    /**
     * @return int|null
     */
    public function getLastEditedBy();

    /**
     * @param int|null $lastEditedBy
     *
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface
     */
    public function setLastEditedBy($lastEditedBy);

    /**
     * @return string
     */
    public function getComment();

    /**
     * @param string $comment
     *
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface
     */
    public function setComment($comment);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface
     */
    public function setStatus($status);
}
