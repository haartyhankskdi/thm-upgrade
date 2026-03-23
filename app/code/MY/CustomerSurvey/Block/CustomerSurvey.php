<?php
namespace MY\CustomerSurvey\Block;

use Magento\Framework\View\Element\Template;
use Magento\Checkout\Model\Session as CheckoutSession;

class CustomerSurvey extends Template
{
    protected $checkoutSession;

    public function __construct(
        Template\Context $context,
        CheckoutSession $checkoutSession,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }

    public function getOrderId()
    {
        return $this->checkoutSession->getLastRealOrderId();
    }

    public function getViewOrderUrl()
    {
        $lastOrderId = $this->checkoutSession->getLastOrderId();
        if (!$lastOrderId) return '';
        return $this->getUrl('sales/order/view', ['order_id' => $lastOrderId]);
    }

    public function getContinueUrl()
    {
        return $this->getUrl('');
    }
}
