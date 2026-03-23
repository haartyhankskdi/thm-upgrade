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

class PaymentMethodsStatus extends ConnectedAccountField
{
    protected $_template    = 'system/config/paymentMethodsStatus.phtml';

    /** @var string */
    public $applePayAssociationFileSetupStatus = '';

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

    /**
     * @param string $key
     * @return bool
     */
    public function isCapabilityActive(string $key): bool
    {
        return !empty($this->stripeConnectedAccount)
            && isset($this->stripeConnectedAccount[Stripe::PARAM_CAPABILITIES][$key])
            && $this->stripeConnectedAccount[Stripe::PARAM_CAPABILITIES][$key] === 'active';
    }


    /**
     * @return bool
     */
    public function isApplePayAssociationFileInPlace(): bool
    {
        try {
            if (!$this->applePayHelper->isDomainAssociationFileInPlace()) {
                $this->applePayHelper->placeDomainAssociationFile();
            }
            return $this->applePayHelper->isDomainAssociationFileInPlace();
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            $this->applePayAssociationFileSetupStatus = $ex->getMessage();
        }
        return false;
    }

    public function getDomainRegistrationUrl()
    {
        return $this->_urlBuilder->getUrl("brippo_payments/applepay/registerstoredomain");
    }
}
