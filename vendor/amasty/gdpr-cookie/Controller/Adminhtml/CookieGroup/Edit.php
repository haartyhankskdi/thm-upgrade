<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Controller\Adminhtml\CookieGroup;

use Amasty\GdprCookie\Controller\Adminhtml\AbstractCookieGroup;
use Amasty\GdprCookie\Model\Repository\CookieGroupsRepository;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;

class Edit extends AbstractCookieGroup
{
    /**
     * @var CookieGroupsRepository
     */
    private $cookieGroupsRepository;

    public function __construct(
        Context $context,
        CookieGroupsRepository $cookieGroupsRepository
    ) {
        parent::__construct($context);
        $this->cookieGroupsRepository = $cookieGroupsRepository;
    }

    /**
     * Edit action
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');

        $title = __('New Cookie Group');

        if ($id) {
            $model = $this->cookieGroupsRepository->getById($id);
            $title = __('Edit Cookie Group %1', $model->getName());
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        if (!$id) {
            $resultPage->getLayout()->unsetElement('store_switcher');
        }

        $resultPage->setActiveMenu('Amasty_GdprCookie::cookie_group');
        $resultPage->addBreadcrumb(__('Cookies'), __('Cookies'));
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
