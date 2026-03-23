<?php

namespace Ebizmarts\SagePaySuite\Block\Form;

class Pi extends \Magento\Payment\Block\Form\Cc
{
    protected $_template = 'Ebizmarts_SagePaySuite::pi-form.html';

    /**
     * @return \Magento\Backend\Model\Session\Quote
     */
    public function getBackendSession()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get(\Magento\Backend\Model\Session\Quote::class);
    }
}
