<?php

namespace Ebizmarts\BrippoPayments\Block\Adminhtml\System\Config;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\ConnectedAccounts as ConnectedAccountsApi;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;

abstract class ConnectedAccountField extends Field
{
    /** @var DataHelper */
    protected $dataHelper;

    /** @var string */
    protected $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

    /** @var int */
    protected $scopeId = 0;

    public $stripeConnectedAccount;

    /** @var Logger */
    public $logger;

    /** @var ConnectedAccountsApi */
    protected $connectedAccountsApi;

    /**
     * @param Context $context
     * @param DataHelper $dataHelper
     * @param Logger $logger
     * @param ConnectedAccountsApi $connectedAccountsApi
     * @param array $data
     */
    public function __construct(
        Context $context,
        DataHelper $dataHelper,
        Logger $logger,
        ConnectedAccountsApi $connectedAccountsApi,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->scope = $this->dataHelper->getScopeTypeFromUrl();
        $this->scopeId = $this->dataHelper->getScopeIdFromUrl();
        $this->logger = $logger;
        $this->connectedAccountsApi = $connectedAccountsApi;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate($this->_template);
        }
        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }

    /**
     * @param bool $liveMode
     * @return bool
     */
    public function hasAccountId(bool $liveMode):bool
    {
        return $this->dataHelper->hasAccountId($this->scopeId, $this->scope, $liveMode);
    }

    /**
     * @param bool $liveMode
     * @return string
     */
    public function retrieveConnectedAccountStatus(bool $liveMode): string
    {
        try {
            $this->stripeConnectedAccount = $this->connectedAccountsApi->get(
                $liveMode,
                $this->dataHelper->getAccountId(
                    $this->scopeId,
                    $liveMode,
                    $this->scope
                )
            );
            return ''; //return empty error
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            return $ex->getMessage();
        }
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->dataHelper->getScopeTypeFromUrl();
    }

    /**
     * @return string
     */
    public function getAccountDoingBusinessAs(): string
    {
        if (isset($this->stripeConnectedAccount[Stripe::PARAM_BUSINESS_PROFILE])) {
            if (isset($this->stripeConnectedAccount[Stripe::PARAM_BUSINESS_PROFILE][Stripe::PARAM_NAME])) {
                return $this->stripeConnectedAccount[Stripe::PARAM_BUSINESS_PROFILE][Stripe::PARAM_NAME];
            } elseif (isset($this->stripeConnectedAccount[Stripe::PARAM_BUSINESS_PROFILE][Stripe::PARAM_URL])) {
                return $this->dataHelper->cleanDomain(
                    $this->stripeConnectedAccount[Stripe::PARAM_BUSINESS_PROFILE][Stripe::PARAM_URL]
                );
            }
        }
        return '';
    }

    /**
     * @return string
     */
    public function getConnectedAccountEmail(): string
    {
        if (isset($this->stripeConnectedAccount[Stripe::PARAM_EMAIL])) {
            return $this->stripeConnectedAccount[Stripe::PARAM_EMAIL];
        }
        return '';
    }
}
