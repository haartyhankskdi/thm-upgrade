<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml\Policy;

use Amasty\Gdpr\Controller\Adminhtml\AbstractPolicy;
use Magento\Framework\Controller\ResultFactory;

class NewAction extends AbstractPolicy
{
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);

        return $result->forward('edit');
    }
}
