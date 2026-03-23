<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Controller\Adminhtml\CookieConsent;

use Amasty\GdprCookie\Controller\Adminhtml\AbstractCookieConsent;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractCookieConsent
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
        $resultPage->setActiveMenu('Amasty_GdprCookie::cookie_consent');
        $resultPage->getConfig()->getTitle()->prepend(__('Cookie Consents Log'));
        $resultPage->addBreadcrumb(__('Cookie Consents Log'), __('Cookie Consents Log'));

        return $resultPage;
    }
}
