<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml\Policy;

use Amasty\Gdpr\Controller\Adminhtml\AbstractPolicy;
use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractPolicy
{
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Gdpr::policy');
        $resultPage->getConfig()->getTitle()->prepend(__('Privacy Policy'));
        $resultPage->addBreadcrumb(__('Privacy Policy'), __('Privacy Policy'));

        return $resultPage;
    }
}
