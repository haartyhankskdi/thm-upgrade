<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MY\CompleteSurvey\Block;

use Magento\Customer\Model\Session;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use MY\CompleteSurvey\Model\CompleteSurveyFactory;

/**
 * Customer Survey manage block
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class CompleteSurvey extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var Session
     */
    private $customer;

    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var CompleteSurveyFactory
     */
    private $completeSurveyFactory;

    public function __construct
    (
        \Magento\Framework\View\Element\Template\Context $context,
        Session $customer,
        CollectionFactory $orderCollectionFactory,
        CompleteSurveyFactory $completeSurveyFactory,
        array $data = []        
    ) {            
        $this->customer = $customer;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->completeSurveyFactory = $completeSurveyFactory;
        parent::__construct($context, $data);
    }

    public function getCustomer(){

        return $this->customer->getCustomer();
    }

    public function getId(){

        return $this->getCustomer()->getId();
    }

    public function getEmail(){

        return $this->getCustomer()->getEmail();
    }

    public function getFirstName(){

        return $this->getCustomer()->getFirstname();
    }

    public function getLastName(){

        return $this->getCustomer()->getLastname();
    }

    /**
     * @return array collection
     */
    public function getCustomerOrder()
    {        
        $customerOrder = $this->orderCollectionFactory->create()
            ->addFieldToFilter('customer_id', $this->getId());
        return $customerOrder;
    }

    /**
     * @return array collection
     */
    public function getCustomerSurveyRecord()
    {        
        $surveyEmailList = [];
        $completesurveymodel = $this->completeSurveyFactory->create();
        $surveyColl = $completesurveymodel->getCollection();
        if($surveyColl->getSize() >= 1) { 
            foreach ($surveyColl as $survey) {             
                $surveyEmailList[] = $survey->getData('customer_email');
             }
        }

        if(in_array($this->getEmail(), $surveyEmailList)){

            return true;
        }else{

            return false;
        }
    }

    /**
     * @return Email Sent
     */
    public function getEmailSent()
    {
       $completesurveymodel = $this->completeSurveyFactory->create();
       $surveyColl = $completesurveymodel->getCollection()->addFieldToFilter('customer_email', ['eq' => $this->getEmail()]);       
       if($surveyColl->getSize() >= 1){
            foreach ($surveyColl as $survey) {
                return $survey->getData('customer_email_sent');
            }
        }
        return false;
    }

    /**
     * @return Survey Id
     */
    public function getSurveyId()
    {
       $completesurveymodel = $this->completeSurveyFactory->create();
       $surveyColl = $completesurveymodel->getCollection()->addFieldToFilter('customer_email', ['eq' => $this->getEmail()]);       
       if($surveyColl->getSize() >= 1){
            foreach ($surveyColl as $survey) {
                return $survey->getData('id');
            }
        }
        return false;
    }
}

