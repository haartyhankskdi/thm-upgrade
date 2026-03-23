<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Block\Adminhtml\Config;

use Amasty\Gdpr\Model\Config;
use Magento\Backend\Block\Template\Context;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\ScopeInterface;

class Editor extends Field
{
    /**
     * @var WysiwygConfig
     */
    private $wysiwygConfig;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        Context $context,
        WysiwygConfig $wysiwygConfig,
        RequestInterface $request,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->wysiwygConfig = $wysiwygConfig;
        $this->request = $request;
    }

    /**
     * Retrieve element HTML
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $params = $this->request->getParams();
        if (isset($params[ScopeInterface::SCOPE_STORE])) {
            $scope = ScopeInterface::SCOPE_STORE;
            $scopeId = $params[ScopeInterface::SCOPE_STORE];
        } elseif (isset($params[ScopeInterface::SCOPE_WEBSITE])) {
            $scope = ScopeInterface::SCOPE_WEBSITE;
            $scopeId = $params[ScopeInterface::SCOPE_WEBSITE];
        } else {
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
            $scopeId = null;
        }

        $configDisplayDpoInfo = $this->_scopeConfig->getValue(
            Config::PATH_PREFIX . '/' . Config::DISPLAY_DPO_INFO,
            $scope,
            $scopeId
        );
        $element->setWysiwyg(true);
        $config = [
            'add_variables' => false,
            'add_widgets' => false
        ];
        $wysiwygConfig = $this->wysiwygConfig->getConfig($config);
        $wysiwygConfig->setAddImages(false);
        if (!$configDisplayDpoInfo) {
            $wysiwygConfig->setData('hidden', true);
        }
        $element->setConfig($wysiwygConfig);

        return parent::_getElementHtml($element);
    }
}
