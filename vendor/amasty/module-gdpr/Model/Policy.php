<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Api\Data\PolicyInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class Policy extends AbstractModel implements PolicyInterface, IdentityInterface
{
    public const CACHE_TAG = 'amgdpr_policy';

    public const STATUS_DRAFT = 2;
    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;

    public function _construct()
    {
        $this->_init(ResourceModel\Policy::class);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->_getData(PolicyInterface::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(PolicyInterface::CREATED_AT, $createdAt);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->_getData(PolicyInterface::UPDATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(PolicyInterface::UPDATED_AT, $updatedAt);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPolicyVersion()
    {
        return $this->_getData(PolicyInterface::POLICY_VERSION);
    }

    /**
     * @inheritdoc
     */
    public function setPolicyVersion($policyVersion)
    {
        $this->setData(PolicyInterface::POLICY_VERSION, $policyVersion);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return $this->_getData(PolicyInterface::CONTENT);
    }

    /**
     * @inheritdoc
     */
    public function setContent($content)
    {
        $this->setData(PolicyInterface::CONTENT, $content);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLastEditedBy()
    {
        return $this->_getData(PolicyInterface::LAST_EDITED_BY);
    }

    /**
     * @inheritdoc
     */
    public function setLastEditedBy($lastEditedBy)
    {
        $this->setData(PolicyInterface::LAST_EDITED_BY, $lastEditedBy);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getComment()
    {
        return $this->_getData(PolicyInterface::COMMENT);
    }

    /**
     * @inheritdoc
     */
    public function setComment($comment)
    {
        $this->setData(PolicyInterface::COMMENT, $comment);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_getData(PolicyInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(PolicyInterface::STATUS, $status);

        return $this;
    }

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
}
