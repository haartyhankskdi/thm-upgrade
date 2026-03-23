<?php

namespace Ebizmarts\BrippoPayments\Block\Adminhtml;

use Magento\Backend\Block\Template;

class BrippoAdmin extends Template
{
    /**
     * @return string
     */
    public function getUrlControllerConfiguration(): string
    {
        return $this->_urlBuilder->getUrl('brippo_payments/configuration/index');
    }

    /**
     * @return string
     */
    public function getUrlControllerOrderLog(): string
    {
        return $this->_urlBuilder->getUrl('brippo_payments/order/log');
    }
}
