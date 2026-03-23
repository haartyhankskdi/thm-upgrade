<?php

namespace Ebizmarts\BrippoPayments\Block\Adminhtml\System\Config;

use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\PlatformService\WebhookMapping;
use Exception;
use Magento\Backend\Block\Template\Context;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\ConnectedAccounts as ConnectedAccountsApi;

class ConnectedAccountStatus extends ConnectedAccountField
{
    protected $_template    = 'system/config/connectedAccountStatus.phtml';

    /** @var bool */
    public $livemode = true;

    /** @var string */
    public $webhookMappingError;

    protected $webhookMappingHelper;

    /**
     * @param Context $context
     * @param DataHelper $dataHelper
     * @param Logger $logger
     * @param WebhookMapping $webhookMappingHelper
     * @param ConnectedAccountsApi $connectedAccountsApi
     * @param array $data
     */
    public function __construct(
        Context $context,
        DataHelper $dataHelper,
        Logger $logger,
        WebhookMapping $webhookMappingHelper,
        ConnectedAccountsApi $connectedAccountsApi,
        array $data = []
    ) {
        parent::__construct($context, $dataHelper, $logger, $connectedAccountsApi, $data);
        $this->webhookMappingHelper = $webhookMappingHelper;
    }

    /**
     * @return bool
     */
    public function isWebhookMappingComplete()
    {
        try {
            $this->webhookMappingHelper->check($this->scope, $this->scopeId, $this->livemode);
            return true;
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            $this->webhookMappingError = $ex->getMessage();
            return false;
        }
    }
}
