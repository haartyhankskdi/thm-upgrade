<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Controller\Adminhtml\Cookie;

use Amasty\GdprCookie\Controller\Adminhtml\AbstractCookie;
use Magento\Framework\Controller\ResultFactory;

class NewAction extends AbstractCookie
{
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);

        return $result->forward('edit');
    }
}
