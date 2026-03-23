<?php
/**
 * Copyright © 2018 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Observer;

use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Ebizmarts\SagePaySuite\Model\Api\Reporting;
use Ebizmarts\SagePaySuite\Model\Config\ModuleVersion;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Plugin\HealthCheck\HealthCheck;
use Ebizmarts\SagePaySuite\Model\Api\PIRest;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class SystemConfigEdit implements ObserverInterface
{
    /**
     * @var Data
     */
    private $_suiteHelper;

    /**
     * @var ManagerInterface
     */
    private $_messageManager;

    /**
     * @var Reporting
     */
    private $_reportingApi;
    /**
     * @var ModuleVersion
     */
    private $moduleVersion = null;
    /**
     * @var Config
     */
    private $config = null;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    private $piRest;

    /**
     * @param Data $suiteHelper
     * @param ManagerInterface $messageManager
     * @param Reporting $reportingApi
     * @param ModuleVersion $moduleVersion
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $suiteHelper,
        ManagerInterface $messageManager,
        Reporting $reportingApi,
        ModuleVersion $moduleVersion,
        Config $config,
        StoreManagerInterface $storeManager,
        PIRest $piRest
    ) {
        $this->_suiteHelper     = $suiteHelper;
        $this->_messageManager  = $messageManager;
        $this->_reportingApi    = $reportingApi;
        $this->moduleVersion    = $moduleVersion;
        $this->config           = $config;
        $this->storeManager     = $storeManager;
        $this->piRest            = $piRest;
    }

    /**
     * Observer payment config section save to validate license and
     * check reporting api credentials.
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $section = $store = $website = null;
        $request = $observer->getEvent()->getRequest();
        $section = $request->getParam('section');
        $store = $request->getParam('store');
        $website = $request->getParam('website');

        if ($store) {
            $scope = 'store';
            $scopeId = $store;
        } elseif ($website) {
            $scope = 'website';
            $scopeId = $website;
        } else {
            $scope = 'default';
            $scopeId = 0;
        }
        if ($section == "payment") {
            if (!$this->isLicenseKeyRegistered()) {
                $errormessage = HealthCheck::ERROR_MESSAGE;
                $this->_messageManager->addWarningMessage(__(
                    $errormessage
                ));
            }
            $this->verifyPiConfig($scope, $scopeId);
            $this->verifyReportingApiCredentialsByCallingVersion();
        }
    }

    private function verifyReportingApiCredentialsByCallingVersion()
    {
        try {
            $this->_reportingApi->getVersion();
        } catch (ApiException $apiException) {
            $message = (string)$apiException->getUserMessage();
            $this->_messageManager->addComplexWarningMessage(
                'invalidCredentials',
                [
                    'exception' => $message
                ]
            );
        } catch (\Exception $e) {
            $this->_messageManager->addWarningMessage(__('Cannot establish connection with Opayo API.'));
        }
    }

    /**
     * @return bool
     */
    private function isLicenseKeyRegistered()
    {
        return $this->_suiteHelper->verify();
    }

    private function verifyPiConfig($scope, $scopeId)
    {
        $paymentsMethod = [ Config::METHOD_PI_MOTO => 'Pi MOTO', Config::METHOD_PI => 'Pi'];
        foreach ($paymentsMethod as $method => $methodName) {
            $isMoto = $method == Config::METHOD_PI_MOTO;
            $this->config->setMethodCode($method);
            if (!$this->config->isMethodActive()) {
                continue;
            }
            $this->piRest->setPaymentMethod($isMoto);
            try {
                $this->piRest->generateMerchantKey($scopeId, $scope);
            } catch (\Exception $e) {
                $message = "Opayo {$methodName} Integration: Could not create Merchant session key with " .
                    "the given vendorname, Pi Key and Pi Password combination." .
                    "For more information on Pi configuration check our";
                $this->_messageManager->addComplexWarningMessage(
                    'invalidCredentials',
                    [
                        'exception' => $message
                    ]
                );
            }
        }
    }
}
