<?php

namespace Ebizmarts\BrippoPayments\Block\Adminhtml\System\Config;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\ConnectedAccounts as ConnectedAccountsApi;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Helper\ApplePay as ApplePayHelper;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Magento\Backend\Block\Template\Context;
use Exception;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Block\Adminhtml\System\Config\ConnectedAccountField;

class PaymentMethodDomainsStatus extends ConnectedAccountField
{
    protected $_template    = 'system/config/paymentMethodDomainsStatus.phtml';

    /** @var ApplePayHelper */
    protected $applePayHelper;

    /** @var bool */
    public $livemode = true;

    public function __construct(
        Context $context,
        DataHelper $dataHelper,
        Logger $logger,
        ApplePayHelper $applePayHelper,
        ConnectedAccountsApi $connectedAccountsApi,
        array $data = []
    ) {
        parent::__construct($context, $dataHelper, $logger, $connectedAccountsApi, $data);
        $this->applePayHelper = $applePayHelper;
    }

    public function getDomainRegistrationUrl()
    {
        return $this->_urlBuilder->getUrl("brippo_payments/domains/registerstoredomain");
    }
}
