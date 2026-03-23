<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MY\Membership\Controller\Manage;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectmanager,
        RedirectFactory $resultRedirectFactory,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->_objectManager = $objectmanager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->pageFactory = $pageFactory;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $blockInstance = $this->_objectManager->get('Mageplaza\RewardPoints\Block\Account\Dashboard');
        $points = explode(' ', $blockInstance->getAvailableBalance(), 1);
        $resultRedirect = $this->resultRedirectFactory->create();
        if($points[0] < 500 || $points[0] == 'zero point'){
            $resultRedirect->setPath('membership-page');
            return $resultRedirect;
        }
        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('VIP Membership Subscription'));

        return $resultPage;
    }
}
