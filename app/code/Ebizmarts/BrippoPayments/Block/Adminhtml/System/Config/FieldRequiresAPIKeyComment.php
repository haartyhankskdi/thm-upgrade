<?php

namespace Ebizmarts\BrippoPayments\Block\Adminhtml\System\Config;

use Ebizmarts\BrippoPayments\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;

class FieldRequiresAPIKeyComment extends Field
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);
        if (empty($this->scopeConfig->getValue(Data::CONFIG_PATH_BRIPPO_API_KEY))) {
            $html .= '<div class="note"><span class="redText">';
            $html .= __('Warning: This feature requires your Brippo API key to be set in the Advanced section.');
            $html .= '</span></div>';
        }
        return $html;
    }
}
