<?php
namespace Kdi\CustomExport\Block\Adminhtml\CustomExport;

class Grid extends \Magento\Backend\Block\Widget\Grid\Container
{
	protected function _construct()
	{
		$this->_blockGroup = 'Kdi_CustomExport';
		$this->_controller = 'adminhtml_customexport';
		$this->_headerText = __('Manage CustomExport');
		
		parent::_construct();
	}
}