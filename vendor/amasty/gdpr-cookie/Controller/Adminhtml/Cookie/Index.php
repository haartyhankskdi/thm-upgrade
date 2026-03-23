<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Controller\Adminhtml\Cookie;

use Amasty\GdprCookie\Controller\Adminhtml\AbstractCookie;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractCookie
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
        $resultPage->setActiveMenu('Amasty_GdprCookie::cookies');
        $resultPage->getConfig()->getTitle()->prepend(__('Cookies'));
        $resultPage->addBreadcrumb(__('Cookies'), __('Cookies'));

        return $resultPage;
    }
}
