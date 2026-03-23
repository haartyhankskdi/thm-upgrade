<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Api\Data;

interface ConsentQueueInterface
{
    public const ID = 'id';
    public const CUSTOMER_ID = 'customer_id';
    public const STATUS = 'status';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\Gdpr\Api\Data\ConsentQueueInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * @param int $customerId
     *
     * @return \Amasty\Gdpr\Api\Data\ConsentQueueInterface
     */
    public function setCustomerId(int $customerId);

    /**
     * @return int
     */
    public function getStatus(): int;

    /**
     * @param int $attemptCount
     *
     * @return \Amasty\Gdpr\Api\Data\ConsentQueueInterface
     */
    public function setStatus(int $status);
}
