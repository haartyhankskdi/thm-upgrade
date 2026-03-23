<?php

namespace Kdi\CustomExport\Controller\Adminhtml\CustomExport;

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
		$resultPage->setActiveMenu('Kdi_CustomExport::customexport_manage');
		
		//Set the header title of grid
		$resultPage->getConfig()->getTitle()->prepend(__('Manage Custom Report'));		//Add bread crumb	
		return $resultPage;
	}	

	/*
	 * Check permission via ACL resource
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Kdi_CustomExport::customexport_manage');
	}
}