<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Haartyhanks\Career\Controller\Post;

use Haartyhanks\Career\Helper\Mail AS HelperMail;

class Index extends \Magento\Framework\App\Action\Action
{
   protected  $logger; 
    protected $resultPageFactory;
    protected $jsonHelper;
    protected $helperEmail;
    protected $_model;
    protected $formKeyValidator;
    /* File Upload */
    protected $_mediaDirectory;
    protected $_fileUploaderFactory;
    protected $messageManager;
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Psr\Log\LoggerInterface $logger,
        \Haartyhanks\Career\Model\CarrerFactory $model,
        HelperMail $helperEmail,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ) {
        /* File Upload */
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        /* Other */
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->helperEmail = $helperEmail;
        $this->_model = $model;
        $this->formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // * validate Form of same domain
        if (!$this->formKeyValidator->validate($this->getRequest())) {
           // $this->messageManager->addErrorMessage(__($getFileArray['message']));
            return $this->_redirect('career');
        }

        $data = $this->getRequest()->getPostValue();

        // Added google captcha
          $secret = '6LctPiEsAAAAAIgXLJTcPDkQgbA97s95VNd4Gaoa';
        $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$data['g-recaptcha-response']);
        $responseData = json_decode($verifyResponse);
        if(!$responseData->success){
            $this->messageManager->addErrorMessage(__("Captcha invalid"));
            return $this->_redirect('career');
        }

        // ! Create Log any where (Remove it if not useful)
        // $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        // $logger = new \Zend\Log\Logger();
        // $logger->addWriter($writer);

        // ? Object manager
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /* Create a new product object */
        $customerSession = $objectManager->create(\Magento\Customer\Model\Session::class);

        //  * Store customer form data in Session for reuse
        $customerSession->setCareerFormData($data);
        try {
            $getFileArray = $this->pleaseUploadFile();

            if($getFileArray['status'] == false){
              
                $this->messageManager->addErrorMessage(__($getFileArray['message']));
                return $this->_redirect('career');
            }
            // * To save save Data into Database
            $model = $this->_model->create();
            $model->setFirstName($data['first_name']);
            $model->setLastName($data['last_name']);
            $model->setPhone($data['phone']);
            $model->setEmail($data['email']);
            $model->setRole($data['role']);
            $model->setLocation($data['location']);
            $data['attachment'] = $getFileArray['message'];

            if(isset($getFileArray['message'])){
                $model->setAttachment($data['attachment']);
                $customerSession->setCareerFormData($data);
            }
            $model->save();
            // * To send email
            $this->helperEmail->sendAdminCareerEmail($data);
            // * to redirect to welcome page.
            $customerSession->unsCareerFormData();
            return $this->_redirect('career-success');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
          

            $this->messageManager->addErrorMessage(__($e->getMessage()));
            // $this->messageManager->addErrorMessage(__('Something wrong while saving form. Please contact the store owner using contact us form.'));
            $this->logger->critical($e->getMessage());
            return $this->_redirect('career');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
            $this->logger->critical($e->getMessage());
            return $this->_redirect('career');
        }
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }

    public function pleaseUploadFile()
    {
        /* 
            For File upload system
        */
        $message = array();
        $message['status'] = false;
        $message['message'] = "Please check uploaded file";
        try{
            $target = $this->_mediaDirectory->getAbsolutePath('career_uploads/');
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->_fileUploaderFactory->create(['fileId' => 'attachment']); 
            $uploader->setAllowedExtensions(['pdf', 'doc', 'docx']);
            /** rename file name if already exists */
            $uploader->setAllowRenameFiles(true);
            /** upload file in folder "mycustomfolder" */
            $result = $uploader->save($target);
            // print_r($uploader);exit();
            if ($result['file']) {
                // print_r($result); exit();
                $pathOfFile = $result['file'];
                $message['status'] = true;
                $message['message'] = $pathOfFile;
                $message['target'] = "gq_uploads/";
                // $this->messageManager->addSuccess(__('File has been successfully uploaded')); 
            }else{
                $message['status'] = false;
                $message['message'] = "Only pdf and word document are allowed";
            }
        } catch (\Exception $e) {
            // $this->messageManager->addError("Please check uploaded file");
            $this->logger->critical($e->getMessage());
            $message['status'] = false;
            $message['message'] = "Please check uploaded file";
        }
        return $message;
    }
}

