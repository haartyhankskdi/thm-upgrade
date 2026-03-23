<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MY\CompleteSurvey\Controller\Manage;

use Magento\Framework\DataObject;
use Magento\Framework\App\Action\Context;
use MY\CompleteSurvey\Model\CompleteSurveyFactory;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;
use Magento\Customer\Model\Session;

class SurveySave extends \Magento\Framework\App\Action\Action
{

    /**
     * @var CompleteSurveyFactory
     */
    protected $completeSurveyFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var BehaviorFactory
     */
    protected $behaviorFactory;

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        CompleteSurveyFactory $completeSurveyFactory,
        Session $customerSession,
        HelperData $helperData,
        BehaviorFactory $behaviorFactory        
    ) {
        parent::__construct($context);
        $this->completeSurveyFactory = $completeSurveyFactory; 
        $this->customerSession = $customerSession; 
        $this->helperData      = $helperData;
        $this->behaviorFactory = $behaviorFactory;  
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $postData = $this->getRequest()->getParams();
        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }        
        try {
            //echo "<pre>";print_r($postData);exit();   
            if($postData['learn_website'] == 'Other'){
                $postData['learn_website'] = $postData['learn_website_text'];
            }
            if($postData['shopping'] == 'Other'){
                $postData['shopping'] = $postData['shopping_text'];
            }
            if($postData['visit_website'] == 'Other'){
                $postData['visit_website'] = $postData['visit_website_text'];
            }
                 
           /* $pointCustSurvey = $this->behaviorFactory->create()->getPointByAction(CustomerEvents::CUSTOMER_SURVEY);            
            if ($pointCustSurvey) {
                try {
                    $this->helperData->getTransaction()->createTransaction(
                        HelperData::ACTION_CUSTOMER_SURVEY,
                        $this->customerSession,
                        new DataObject(['point_amount' => 10])
                    );
                } catch (Exception $e) {
                    $this->logger->critical($e->getMessage());
                } */    
          //  }     
            $custEmail = $this->customerSession->getCustomer()->getEmail();                 
            $completesurveymodel = $this->completeSurveyFactory->create();
            $completesurveymodel->load($custEmail, 'customer_email');  
            $completesurveymodel->setData($postData);
            $completesurveymodel->save();  
            $this->messageManager->addSuccessMessage(__('Thank you for completing our survey!'));       
            return $this->resultRedirectFactory->create()->setPath('surveysuccess');             
        } catch (\Exception $e) {            
            $this->messageManager->addErrorMessage(__('Can\'t save survey answer. Please try again!'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');      
        }                          
    }
}
