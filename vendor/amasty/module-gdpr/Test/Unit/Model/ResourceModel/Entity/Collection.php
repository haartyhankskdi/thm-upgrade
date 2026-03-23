<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Test\Unit\Model\ResourceModel\Entity;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Load collection by customer id
     *
     * @param int $id
     * @return $this
     */
    public function filterByCustomerId($id)
    {
        return $this;
    }

    /**
     * Load collection by customer id
     *
     * @return $this
     */
    public function filterByActive()
    {
        return $this;
    }
}
