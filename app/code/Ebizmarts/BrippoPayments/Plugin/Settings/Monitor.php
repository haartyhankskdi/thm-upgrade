<?php 

namespace Ebizmarts\BrippoPayments\Plugin\Settings;

use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Ebizmarts\BrippoPayments\Model\PaymentElement;
use Ebizmarts\BrippoPayments\Model\ExpressCheckoutElement;
use Ebizmarts\BrippoPayments\Model\Express;
use Ebizmarts\BrippoPayments\Model\PayByLink;
use Ebizmarts\BrippoPayments\Model\PayByLinkMoto;
use Ebizmarts\BrippoPayments\Model\TerminalBackend;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\PlatformService\SettingsMonitor;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;

class Monitor {
    /** @var ScopeConfigInterface  */
    private $scopeConfig;

    /** @var Json */
    private $json;

    /** @var Logger */
    private $logger;

    /** @var SettingsMonitor */
    private $settingsMonitor;

    /** @var DataHelper */
    protected $dataHelper;

    /**
     * Monitor constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     * @param Logger $logger
     * @param SettingsMonitor $settingsMonitor
     * @param DataHelper $dataHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $json,
        Logger $logger,
        SettingsMonitor $settingsMonitor,
        DataHelper $dataHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->logger = $logger;
        $this->settingsMonitor = $settingsMonitor;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param Config $subject
     * @param callable $proceed
     * @return mixed
     */
    public function aroundSave(Config $subject, callable $proceed)
    {
        if ($subject->getSection() != 'payment') {
            return $proceed();
        }

        $peEnabledBefore = $this->scopeConfig->getValue(PaymentElement::XML_PATH_ACTIVE);
        $eceEnabledBefore = $this->scopeConfig->getValue(ExpressCheckoutElement::XML_PATH_ACTIVE);
        $expressEnabledBefore = $this->scopeConfig->getValue(Express::XML_PATH_ACTIVE);
        $payByLinkEnabledBefore = $this->scopeConfig->getValue(PayByLink::XML_PATH_ACTIVE);
        $payByLinkMotoEnabledBefore = $this->scopeConfig->getValue(PayByLinkMoto::XML_PATH_ACTIVE);
        $terminalBackendEnabledBefore = $this->scopeConfig->getValue(TerminalBackend::XML_PATH_ACTIVE);
        $result = $proceed();
        $peEnabled = $this->scopeConfig->getValue(PaymentElement::XML_PATH_ACTIVE);
        $eceEnabled = $this->scopeConfig->getValue(ExpressCheckoutElement::XML_PATH_ACTIVE);
        $expressEnabled = $this->scopeConfig->getValue(Express::XML_PATH_ACTIVE);
        $payByLinkEnabled = $this->scopeConfig->getValue(PayByLink::XML_PATH_ACTIVE);
        $payByLinkMotoEnabled = $this->scopeConfig->getValue(PayByLinkMoto::XML_PATH_ACTIVE);
        $terminalBackendEnabled = $this->scopeConfig->getValue(TerminalBackend::XML_PATH_ACTIVE);


        $disabledPM = [];

        if ((int)$peEnabled == 0) {
            $disabledPM[] = PaymentElement::METHOD_CODE;
        }
        if ((int)$eceEnabled == 0) {
            $disabledPM[] = ExpressCheckoutElement::METHOD_CODE;
        }
        if ((int)$expressEnabled == 0) {
            $disabledPM[] = Express::METHOD_CODE;
        }
        if ((int)$payByLinkEnabled == 0) {
            $disabledPM[] = PayByLink::METHOD_CODE;
        }
        if ((int)$payByLinkMotoEnabled == 0) {
            $disabledPM[] = PayByLinkMoto::METHOD_CODE;
        }
        if ((int)$terminalBackendEnabled== 0) {
            $disabledPM[] = TerminalBackend::METHOD_CODE;
        }

        $settingsChanged = $peEnabledBefore != $peEnabled
            || $eceEnabledBefore != $eceEnabled
            || $expressEnabled != $expressEnabledBefore
            || $payByLinkEnabled != $payByLinkEnabledBefore
            || $payByLinkMotoEnabled != $payByLinkMotoEnabledBefore
            || $terminalBackendEnabled != $terminalBackendEnabledBefore;

        try {
            $scopeId = $this->dataHelper->getScopeIdFromUrl();
            $liveMode = $this->dataHelper->isLiveMode($scopeId);

            if (count($disabledPM) > 0 && $settingsChanged) {
                $settingsJson = $this->json->serialize($subject->getData('groups')["brippo_payments"]["groups"]);
                $this->settingsMonitor->sendNotification(
                    $this->dataHelper->getScopeTypeFromUrl(),
                    $scopeId,
                    $liveMode,
                    $settingsJson,
                    $disabledPM
                );
            }
        } catch (\Exception $e) {
            $this->logger->log("Settings monitor error: " . $e->getMessage());
            $result = $proceed();
        }

        return $result;
    }
}
