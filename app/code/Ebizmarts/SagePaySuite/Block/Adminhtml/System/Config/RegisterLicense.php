<?php

namespace Ebizmarts\SagePaySuite\Block\Adminhtml\System\Config;

use \Magento\Config\Block\System\Config\Form\Field;
use \Magento\Framework\Data\Form\Element\AbstractElement;
use Ebizmarts\SagePaySuite\Model\Config\ModuleVersion;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Helper\Data;

class RegisterLicense extends Field
{
    /**
     * @var Config
     */
    private $config = null;
    /**
     * @var ModuleVersion
     */
    private $moduleVersion = null;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Config $config
     * @param ModuleVersion $moduleVersion
     * @param Logger $logger
     * @param array $data
     * @param Data $helper
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Config $config,
        ModuleVersion $moduleVersion,
        Logger $logger,
        Data $helper,
        array $data = []
    ) {
        $this->config = $config;
        $this->moduleVersion = $moduleVersion;
        $this->storeManager = $context->getStoreManager();
        $this->logger = $logger;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('system/config/registerlicense.phtml');
    }
    protected function _getElementHtml(AbstractElement $element)
    {
        $label = $this->getLabel($element);
        $this->addData(
            [
                'button_label' => __($label),
                'button_url' => $this->getAjaxCheckUrl(),
                'html_id' => $element->getHtmlId(),
            ]
        );
        return $this->_toHtml();
    }

    public function getButtonHtml(AbstractElement $element)
    {
        $label = $this->getLabel($element);
        $this->addData([
            'button_label' => __($label),
            'button_url'   => $this->getAjaxCheckUrl(),
            'html_id' => $element->getHtmlId(),
        ]);
        return $this->_toHtml();
    }
    protected function getLabel(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $label = $originalData['button_label'];
        return $label;
    }
    public function getAjaxCheckUrl()
    {
        $params = $this->getRequest()->getParams();
        $scope = [];
        if (isset($params['website'])) {
            $scope = ['website'=>$params['website']];
        } elseif (isset($params['store'])) {
            $scope = ['store'=>$params['store']];
        }

        $params = [
            '_nosid' => true,
            '_secure' => true,
            '_store' => $this->config->getCurrentStoreId()
        ];

        $params = array_merge_recursive($params, $scope);

        return $this->_urlBuilder->getUrl('sagepaysuite/config/RegisterLicense', $params);
    }
    public function needToRegister()
    {
        return !$this->helper->verify();
    }
    public function getScope()
    {
        $params = $this->getRequest()->getParams();
        $scope = 'default';
        if (isset($params['website'])) {
            $scope = 'website';
        } elseif (isset($params['store'])) {
            $scope = 'store';
        }
        return $scope;
    }
    public function getScopeId()
    {
        $params = $this->getRequest()->getParams();
        $scopeId = 0;
        if (isset($params['website'])) {
            $scopeId = $params['website'];
        } elseif (isset($params['store'])) {
            $scopeId = $params['store'];
        }
        return $scopeId;
    }

    public function getPrefix()
    {
        $htmlId = $this->getHtmlId();
        $prefix = str_replace("_sagepaysuite_global_register_licence_key", "", $htmlId);
        return $prefix;
    }

    public function getConfigurationUrl()
    {
        $configurationUrl = "https://wiki.ebizmarts.com/ebizmarts-payments-opayo/m2-configuration-guide";
        return $configurationUrl;
    }
}
