<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml\ConsentLog;

use Magento\Framework\Controller\ResultFactory;

use Amasty\Gdpr\Controller\Adminhtml\AbstractConsentLog;

class Index extends AbstractConsentLog
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Gdpr::consent_log');
        $resultPage->getConfig()->getTitle()->prepend(__('Consent Log'));
        $resultPage->addBreadcrumb(__('Consent Log'), __('Consent Log'));

        return $resultPage;
    }
}
