<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Config\Source\Order;

class Status extends \Magento\Sales\Model\Config\Source\Order\Status
{
    /**
     * @var array
     */
    protected $_stateStatuses = [];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $orderStatuses = parent::toOptionArray();
        unset($orderStatuses[0]);

        return $orderStatuses;
    }
}
