<?php

namespace MY\CompleteSurvey\Controller\Adminhtml\CompleteSurvey;

class Index extends \Magento\Backend\App\Action
{
	protected $resultPageFactory = false;

	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	) {
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
	}	

	public function execute()
	{
		//Call page factory to render layout and page content
		$resultPage = $this->resultPageFactory->create();		//Set the menu which will be active for this page
		$resultPage->setActiveMenu('MY_CompleteSurvey::completesurvey_manage');
		
		//Set the header title of grid
		$resultPage->getConfig()->getTitle()->prepend(__('Manage Post Sales Customer Survey'));		//Add bread crumb	
		return $resultPage;
	}	

	/*
	 * Check permission via ACL resource
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('MY_CompleteSurvey::completesurvey_manage');
	}
}