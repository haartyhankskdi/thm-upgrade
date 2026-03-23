<?php

namespace MY\CompleteSurvey\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use MY\CompleteSurvey\Model\CompleteSurveyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey;

class EmailSend
{

	/**
    * @var CollectionFactory 
    */
    protected $orderCollectionFactory;

    /**
    * @var CollectionFactory 
    */
    protected $completeSurveyFactory;

    protected $inlineTranslation;
    protected $escaper;
    protected $transportBuilder;
    protected $logger;
    protected $scopeConfig;
    protected $formKey;

   /**
    * OrderDetails constructor.
    * @param CollectionFactory  $orderCollectionFactory
    */
    public function __construct
    (
        CollectionFactory  $orderCollectionFactory,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        CompleteSurveyFactory $completeSurveyFactory,
        ScopeConfigInterface $scopeConfig,
        FormKey $formKey,
        Context $context
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder; 
        $this->completeSurveyFactory = $completeSurveyFactory; 
        $this->_scopeConfig = $scopeConfig;  
        $this->formKey = $formKey;      
        $this->logger = $context->getLogger();
    }

	public function execute()
	{
        $surveyEmailList = [];
        $emailSent = $custEmailSent = '';
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/surveycron.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $emailResponse = $emailResponse1 = '';
        $currentDate = date("Y-m-d h:i:s");
		$today = date("Y-m-d"); // current date
        $days_ago = date('Y-m-d', strtotime('-4 days', strtotime($today))); 
        $completesurveymodel = $this->completeSurveyFactory->create();  
        $surveyColl = $completesurveymodel->getCollection(); 
        if($surveyColl->getSize() >= 1) { 
            foreach ($surveyColl as $survey) {             
                $surveyEmailList[] = $survey->getData('customer_email');
            }   
        }       
        $collection = $this->_orderCollectionFactory->create()->addFieldToSelect(['customer_id','customer_firstname','customer_lastname','customer_email'])->addAttributeToFilter('customer_is_guest', ['eq'=>0])->addAttributeToFilter('created_at', ['gteq'=>$days_ago.' 00:00:00'])->addAttributeToFilter('created_at', ['lteq'=>$days_ago.' 23:59:59']);
        $collection->getSelect()->group('customer_email');        
        if($collection->getSize() >= 1){            
            foreach ($collection as $collData) {  
                $custEmailColl = $surveyColl->addFieldToFilter('customer_email', ['eq' => $collData->getData('customer_email')]);
                if($custEmailColl->getSize() >= 1){
                    foreach ($custEmailColl as $custEmail) {
                            $custEmailSent = $custEmail->getData('customer_email_sent');
                    }  
                }               
                if(!in_array($collData->getData('customer_email'), $surveyEmailList)){                      
                    $emailResponse = $this->sendEmail($collData->getData('customer_email'),$collData->getData('customer_firstname'),$collData->getData('customer_lasttname'),$collData->getData('customer_id'));
                    $emailResponse = true;
                    if($emailResponse){
                        try{
                            $emailSent = 'yes';
                            $completesurveymodel1 = $this->completeSurveyFactory->create();
                            $completesurveymodel1->setCustomerEmail($collData->getData('customer_email'));
                            $completesurveymodel1->setCustomerEmailSent($emailSent);
                            $completesurveymodel1->save();  
                        }catch (\Exception $e) {
                            $this->logger->debug($e->getMessage());
                        }
                        $logger->info("Email Send Successfully --> ".'Email --> '.$collData->getData('customer_email').' Date ---> '.$currentDate);
                    }else{
                        try{
                            $emailSent = 'no';
                            $completesurveymodel2 = $this->completeSurveyFactory->create();
                            $completesurveymodel2->setCustomerEmail($collData->getData('customer_email'));
                            $completesurveymodel2->setCustomerEmailSent($emailSent);
                            $completesurveymodel2->save();  
                        }catch (\Exception $e) {
                            $this->logger->debug($e->getMessage());
                        } 
                        $logger->info("Email Not Send --> ".'Email --> '.$collData->getData('customer_email').' Date ---> '.$currentDate);
                    }
                }elseif($custEmailSent && $custEmailSent != 'yes' && !empty($custEmailSent) && $custEmailSent == ''){   
                    $emailResponse1 = $this->sendEmail($collData->getData('customer_email'),$collData->getData('customer_firstname'),$collData->getData('customer_lasttname'),$collData->getData('customer_id'));
                    if($emailResponse1){
                        try{
                            $emailSent = 'yes';       
                            $completesurveymodel3 = $this->completeSurveyFactory->create();                     
                            $completesurveymodel3->setCustomerEmailSent($emailSent);
                            $completesurveymodel3->save();  
                        }catch (\Exception $e) {
                            $this->logger->debug($e->getMessage());
                        }
                        $logger->info("Email Send again Successfully --> ".'Email --> '.$collData->getData('customer_email').' Date ---> '.$currentDate);
                    }
                }else{
                    $logger->info('Email Already Sent --->'.$collData->getData('customer_email').' Date ---> '.$currentDate);
                }                
            }
        }else{
            $logger->info("No Emails To Sent".' Date ---> '.$currentDate);
        }	        		        

		return $this;
	}

    public function sendEmail($emailto,$custfname,$custlname,$custid)
    {
        try {  
            $custName = $custfname.' '.$custlname; 
            $custIdEncoded = base64_encode($custid);
            $custEmailEncoded = base64_encode($emailto);        
            $this->inlineTranslation->suspend();
            $sender = [
                'name' => $this->escaper->escapeHtml($this->_scopeConfig->getValue('trans_email/ident_sales/name',\Magento\Store\Model\ScopeInterface::SCOPE_STORE)),
                'email' => $this->escaper->escapeHtml($this->_scopeConfig->getValue('trans_email/ident_sales/email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE)),
            ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('order_email_cron')
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars([
                    'templateVar'  => 'My Topic',
                    'custname' => $custName,
                    'custemail' => $custEmailEncoded,
                    'formkey' => $this->getFormKey(), 
                    'custid' => $custIdEncoded,                 
                ])
                ->setFrom($sender)
                ->addTo($emailto)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            return true;
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
