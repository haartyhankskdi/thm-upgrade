<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Api\Data\ConsentQueueInterface;
use Amasty\Gdpr\Model\ResourceModel\ConsentQueue as ConsentEmailQueueResource;
use Magento\Framework\Model\AbstractModel;

class ConsentQueue extends AbstractModel implements ConsentQueueInterface
{
    public const STATUS_PENDING = 0;
    public const STATUS_SUCCESS = 1;
    public const STATUS_FAIL = 2;

    public function _construct()
    {
        $this->_init(ConsentEmailQueueResource::class);
    }

    public function getCustomerId(): int
    {
        return (int)$this->_getData(ConsentQueueInterface::CUSTOMER_ID);
    }

    public function setCustomerId(int $customerId)
    {
        $this->setData(ConsentQueueInterface::CUSTOMER_ID, $customerId);

        return $this;
    }

    public function getStatus(): int
    {
        return (int)$this->_getData(ConsentQueueInterface::STATUS);
    }

    public function setStatus(int $status)
    {
        $this->setData(ConsentQueueInterface::STATUS, $status);

        return $this;
    }
}
