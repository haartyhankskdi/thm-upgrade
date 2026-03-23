<?php

namespace MY\CustomerSurvey\Controller\Survey;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use MY\CustomerSurvey\Model\CustomerSurveyFactory;

class Save extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    protected $_checkoutSession;
    protected $customerSurveyFactory;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        CustomerSurveyFactory $customerSurveyFactory
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->customerSurveyFactory = $customerSurveyFactory;
        parent::__construct($context);
    }

    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null; // allow normal form key validation
    }

    public function validateForCsrf(RequestInterface $request): ?bool {
        return true;
    }

    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        try {
            $postData = $this->getRequest()->getParams();
            $order = $this->_checkoutSession->getLastRealOrder();
            $orderId = $order->getIncrementId();

            $customersurveymodel = $this->customerSurveyFactory->create();
            $customersurveymodel->setOrderId($orderId);
            $customersurveymodel->setWebsiteNavigation($postData['website_navigation']);
            $customersurveymodel->setImprovement($postData['improvement']);
            $customersurveymodel->save();

            $this->messageManager->addSuccessMessage(__('Thank you for completing our survey!'));
            return $this->resultRedirectFactory->create()->setPath('surveysuccess');
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Can\'t save survey answer. Please try again!'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
    }
}
