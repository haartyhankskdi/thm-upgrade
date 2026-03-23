<?php

namespace Ebizmarts\BrippoPayments\Block\Onepage;

use Ebizmarts\BrippoPayments\Model\PayByLink;
use Ebizmarts\BrippoPayments\Model\PaymentElement;
use Magento\Checkout\Block\Onepage\Success as OnepageSuccess;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Sales\Model\Order\Config;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;

class Success extends OnepageSuccess
{
    protected $dataHelper;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        Config $orderConfig,
        HttpContext $httpContext,
        DataHelper $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
        $this->dataHelper = $dataHelper;
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function isBrippoFrontendPayByLink()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        if ($order && !empty($order->getPayment()) &&
            $order->getPayment()->getMethodInstance()->getCode() === PayByLink::METHOD_CODE) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isBrippoPaymentElement()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        if ($order && !empty($order->getPayment()) &&
            $order->getPayment()->getMethodInstance()->getCode() === PaymentElement::METHOD_CODE) {
            return true;
        }

        return false;
    }

    public function getPayByLinkSuccessNote()
    {
        return $this->dataHelper->getStoreConfig(
            PayByLink::XML_PATH_STORE_CONFIG_SUCCESS_NOTE,
            $this->_storeManager->getStore()->getId()
        );
    }
}
