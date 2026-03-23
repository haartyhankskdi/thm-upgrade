<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Api\Data\ActionLogInterface;
use Magento\Framework\Model\AbstractModel;

class ActionLog extends AbstractModel implements ActionLogInterface
{
    public function _construct()
    {
        $this->_init(ResourceModel\ActionLog::class);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->_getData(ActionLogInterface::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($customerId)
    {
        $this->setData(ActionLogInterface::CUSTOMER_ID, $customerId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->_getData(ActionLogInterface::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(ActionLogInterface::CREATED_AT, $createdAt);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIp()
    {
        return $this->_getData(ActionLogInterface::IP);
    }

    /**
     * @inheritdoc
     */
    public function setIp($ip)
    {
        $this->setData(ActionLogInterface::IP, $ip);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAction()
    {
        return $this->_getData(ActionLogInterface::ACTION);
    }

    /**
     * @inheritdoc
     */
    public function setAction($action)
    {
        $this->setData(ActionLogInterface::ACTION, $action);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getComment(): string
    {
        return (string)$this->_getData(ActionLogInterface::COMMENT);
    }

    /**
     * @inheritdoc
     */
    public function setComment(string $comment)
    {
        $this->setData(ActionLogInterface::COMMENT, $comment);

        return $this;
    }
}
