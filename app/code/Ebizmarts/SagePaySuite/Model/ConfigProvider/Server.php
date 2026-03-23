<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\ConfigProvider;

use AWS\CRT\Log;
use Ebizmarts\SagePaySuite\Model\Token\VaultDetailsHandler;
use Magento\Checkout\Model\ConfigProviderInterface;
use \Ebizmarts\SagePaySuite\Model\Config;
use Magento\Payment\Helper\Data as PaymentHelper;

class Server implements ConfigProviderInterface
{
    /**
     * @var string
     */
    private $methodCode = Config::METHOD_SERVER;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Server
     */
    private $method;

    /**
     * @var \Ebizmarts\SagePaySuite\Helper\Data
     */
    private $_suiteHelper;

    /** @var \Magento\Customer\Model\Session */
    private $_customerSession;

    /** @var \Ebizmarts\SagePaySuite\Model\Token */
    private $_tokenModel;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config
     */
    private $_config;
    /**
     * @var VaultDetailsHandler
     */
    private $_vaultDetailsHandler;

    /**
     * @param PaymentHelper $paymentHelper
     * @param \Ebizmarts\SagePaySuite\Helper\Data $suiteHelper
     * @param \Ebizmarts\SagePaySuite\Model\Token $tokenModel
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Config $config
     * @param VaultDetailsHandler $_vaultDetailsHandler
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        \Ebizmarts\SagePaySuite\Helper\Data $suiteHelper,
        \Ebizmarts\SagePaySuite\Model\Token $tokenModel,
        \Magento\Customer\Model\Session $customerSession,
        Config $config,
        VaultDetailsHandler $_vaultDetailsHandler
    ) {
        $this->_customerSession = $customerSession;
        $this->_tokenModel      = $tokenModel;
        $this->method           = $paymentHelper->getMethodInstance($this->methodCode);
        $this->_suiteHelper     = $suiteHelper;
        $this->_config          = $config;
        $this->_vaultDetailsHandler = $_vaultDetailsHandler;
    }

    public function getConfig()
    {
        if (!$this->method->isAvailable()) {
            return [];
        }

        $this->_config->setMethodCode(Config::METHOD_SERVER);
        //get tokens if enabled and cutomer is logged in
        $tokenEnabled = (bool)$this->_config->isTokenEnabled();
        $tokens = null;
        if ($tokenEnabled) {
            if (!empty($this->_customerSession->getCustomerId())) {
                $tokens = $this->_vaultDetailsHandler->getTokensFromCustomerToShowOnGrid(
                    $this->_customerSession->getCustomerId(),
                    'sagepaysuite',
                    $this->_config->getVendorname()
                );
                $tokenEnabled = true;
            } else {
                $tokenEnabled = false;
            }
        }

        return [
            'payment' => [
                'ebizmarts_sagepaysuiteserver' => [
                'licensed'      => $this->_suiteHelper->verify(),
                'token_enabled' => $tokenEnabled,
                'tokens'        => $tokens,
                'max_tokens'    => $this->_config->getMaxTokenPerCustomer(),
                'mode'          => $this->_config->getMode(),
                'low_profile'   => (bool)$this->method->getConfigData('profile'),
                'payment_layout'=> $this->_config->getPaymentLayout()
                ],
            ]
        ];
    }
}
