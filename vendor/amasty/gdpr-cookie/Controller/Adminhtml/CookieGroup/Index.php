<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Controller\Adminhtml\CookieGroup;

use Amasty\GdprCookie\Controller\Adminhtml\AbstractCookieGroup;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractCookieGroup
{
    /**
     * Index action
     *
     * @return Page
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_GdprCookie::cookie_group');
        $resultPage->getConfig()->getTitle()->prepend(__('Cookie Groups'));
        $resultPage->addBreadcrumb(__('Cookie Groups'), __('Cookie Groups'));

        return $resultPage;
    }
}
