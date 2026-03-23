<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

use Magento\Framework\Model\AbstractModel;

class PolicyContent extends AbstractModel
{
    public function _construct()
    {
        $this->_init(ResourceModel\PolicyContent::class);
    }
}
