<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MY\Membership\Controller\Manage;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\SessionFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Message\ManagerInterface;


class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param AccountManagementInterface $customerAccountManagement
     */
    public function __construct(
        Context $context,
        SessionFactory $customerSession,
        LoggerInterface $logger,
        RedirectFactory $resultRedirectFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        ManagerInterface $messageManager,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession->create();
        $this->logger = $logger;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->customerRepository = $customerRepository;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->messageManager = $messageManager;
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
        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('VIP Membership Subscription'));

        $resultRedirect = $this->resultRedirectFactory->create();

        //echo "<pre>"; print_r($this->getRequest()->getParams()); exit();

        $vipcustomergroup = $this->getRequest()->getPost('vipcustomergroup');
        $vipcustomerpass = $this->getRequest()->getPost('vip_cpass');

        if($vipcustomergroup && $vipcustomergroup != ''){
            $groupId = 4;
        }
        
        $customer = $this->customerRepository->getById($this->getLoggedinCustomerId());
                
        try {
            $customerAuthenticate = $this->customerAccountManagement->authenticate($this->getLoggedinCustomerEmail(), $vipcustomerpass);
            $this->_customerSession->setCustomerDataAsLoggedIn($customerAuthenticate);
        } catch (\Exception $e) {
            //Authentication Failed
            $this->messageManager->addError(__("Password Incorrect"));
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }        


        if ($customer) {            
            try {
                $customer->setGroupId($groupId);
                $this->customerRepository->save($customer);                
                $this->messageManager->addSuccess(__("Upgraded to VIP Membership"));                
                $resultRedirect->setPath('membership_thankyou');
            } catch (LocalizedException $exception) {
             $this->logger->error($exception);
            }
        }        
        return $resultRedirect;
    }

    public function getLoggedinCustomerId() {        
        if ($this->_customerSession->isLoggedIn()) {            
            return $this->_customerSession->getCustomer()->getId();
        }        
        return false;
    }

    public function getLoggedinCustomerEmail() {
        if ($this->_customerSession->isLoggedIn()) {
            return $this->_customerSession->getCustomer()->getEmail();
        }
        return false;
    }
}
