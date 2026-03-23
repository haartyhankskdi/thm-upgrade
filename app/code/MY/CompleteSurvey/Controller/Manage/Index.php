<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MY\CompleteSurvey\Controller\Manage;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use MY\CompleteSurvey\Model\CompleteSurveyFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\App\Response\RedirectInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var CompleteSurveyFactory
     */
    protected $completeSurveyFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * @var RedirectInterface
     */
    protected $redirect;
    protected $_storeManager;

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,        
        PageFactory $pageFactory,
        Session $customerSession,
        StoreManagerInterface $storeManagerInterface,
        CompleteSurveyFactory $completeSurveyFactory,
        CollectionFactory $orderCollectionFactory,
        RedirectInterface $redirect
    ) {
        parent::__construct($context);        
        $this->pageFactory = $pageFactory;
        $this->customerSession = $customerSession; 
        $this->_storeManager = $storeManagerInterface; 
        $this->completeSurveyFactory = $completeSurveyFactory; 
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->redirect = $redirect;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {      
        $values = parse_url($this->redirect->getRefererUrl());
        $host = explode('.',$values['path']);
        $surveyCustFname = $surveyCustLname = $surveyCustEmail = '';        
        $cIdParam = $this->getRequest()->getParam('cid');
        $cEmailParam = $this->getRequest()->getParam('cemail');
        $custIdParam = $cIdParam !== null ? base64_decode($cIdParam) : null;

        $custEmailParam = $cEmailParam !== null ? base64_decode($cEmailParam) : null;
        $custIdSession = $this->customerSession->getCustomer()->getId();
        $custEmailSession = $this->customerSession->getCustomer()->getEmail();
        $surveyUrl = base64_encode($this->_storeManager->getStore()->getBaseUrl().'completesurvey/manage');
        $params = array(
            'referer' => $surveyUrl,
            'urlParam' => $cIdParam
        );
        $hostArray = ['/sales/order/history/','/downloadable/customer/products/','/customer/address/','/customer/account/edit/','/vault/cards/listaction/','/review/customer/','/newsletter/manage/','/membership/manage/'];
        if (!$this->customerSession->isLoggedIn()) {
            return $this->_redirect('customer/account/login/', $params);
        }else{
            if(!in_array($host[0], $hostArray)){
                if(!$custIdParam || $custIdParam == '' || empty($custIdParam)){
                    if($custEmailParam != $custEmailSession){
                        $this->messageManager->addErrorMessage(__('Since you may be have deleted your account! So please go through the email link sent to ur registered email id')); 
                        return $this->resultRedirectFactory->create()->setPath('customer/account');
                    }
                }else{
                    if($custIdParam != $custIdSession){
                        $this->messageManager->addErrorMessage(__('Please go through the email link sent to ur registered email id')); 
                        return $this->resultRedirectFactory->create()->setPath('customer/account');  
                    }
                }
            }
        }     
        
        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Customer Survey'));

        $custEmail = $this->customerSession->getCustomer()->getEmail();
        $completesurveymodel = $this->completeSurveyFactory->create(); 
        $surveyColl = $completesurveymodel->getCollection();
        if($surveyColl->getSize() >= 1) { 
            foreach ($surveyColl as $survey) {             
                $surveyCustEmail = $survey->getData('customer_email');
                $surveyCustFname = $survey->getData('customer_fname');
                $surveyCustLname = $survey->getData('customer_lname');
             }
        }
         //echo $custEmail;
        if($this->getCustomerOrder()->getSize() >= 1){
            if($surveyCustEmail && $surveyCustFname && $surveyCustLname && $surveyCustEmail != '' && $surveyCustFname != '' && $surveyCustLname != '' && !empty($surveyCustFname) && !empty($surveyCustLname) && !empty($surveyCustEmail)){
                $this->messageManager->addSuccessMessage(__('You have already submitted survey! Thank You!')); 
                return $this->resultRedirectFactory->create()->setPath('customer/account');
            }  
        }else{
            $this->messageManager->addSuccessMessage(__('You need to have atleast one order to submit survey! Thank You!')); 
            return $this->resultRedirectFactory->create()->setPath('customer/account');
        }       
        return $resultPage;
    }

    /**
     * @return array collection
     */
    public function getCustomerOrder()
    {        
        $customerOrder = $this->orderCollectionFactory->create()
            ->addFieldToFilter('customer_id', $this->customerSession->getCustomer()->getId());
        return $customerOrder;
    }
}
