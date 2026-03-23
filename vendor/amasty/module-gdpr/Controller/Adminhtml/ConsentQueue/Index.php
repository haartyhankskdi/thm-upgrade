<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml\ConsentQueue;

use Amasty\Gdpr\Controller\Adminhtml\AbstractConsentQueue;
use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractConsentQueue
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Gdpr::consent_queue');
        $resultPage->getConfig()->getTitle()->prepend(__('Customers Consents Email Queue'));
        $resultPage->addBreadcrumb(__('Customers Consents Email Queue'), __('Customers Consents Email Queue'));

        return $resultPage;
    }
}
