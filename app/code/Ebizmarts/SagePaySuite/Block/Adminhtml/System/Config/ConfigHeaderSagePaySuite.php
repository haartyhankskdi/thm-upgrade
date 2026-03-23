<?php

namespace Ebizmarts\SagePaySuite\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Helper\Js;
use Magento\Store\Model\ScopeInterface;
use Ebizmarts\SagePaySuite\Model\Config\ModuleVersion;

class ConfigHeaderSagePaySuite extends Fieldset
{
    /**
     * @var ModuleVersion
     */
    private $moduleVersion;

    /**
     * @param Context $context
     * @param Session $authSession
     * @param Js $jsHelper
     * @param array $data
     * @param ModuleVersion $moduleVersion
     */
    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        ModuleVersion $moduleVersion,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $authSession,
            $jsHelper,
            $data
        );
        $this->moduleVersion = $moduleVersion;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getFrontendClass($element)
    {
        return parent::_getFrontendClass($element) . ' with-button';
    }

    /**
     * @param $element
     * @return string
     * @throws NoSuchEntityException
     */
    protected function _getHeaderTitleHtml($element)
    {
        $html = '
        <div class="config-heading" >';
        $htmlId = $element->getHtmlId();
        $html .= '<div class="button-container"><button type="button"' .
            ' class="button action-configure' .
            '" id="' . $htmlId . '-head" >' .
            '<span class="state-closed">' . __(
                'Configure'
            ) . '</span><span class="state-opened">' . __(
                'Close'
            ) . '</span></button>';

        $html .= '</div>';
        $html .= '<div class="heading"><strong>' . $element->getLegend() . '</strong>';
        $html .= '<span class="heading-intro">' .
            '<div class="sagepay-payment-logo"><div class="logo"></div></div>' .
            '<div class="sagepay-payment-text">' .
            '<strong>
                <a target="_blank" href="https://ebizmarts.com"><b>ebizmarts</b></a> ' .
                __('is an Opayo approved partner').
                ' <a target="_blank" href="https://referrals.elavon.co.uk/?partner_id=0014H00004E0itk" ' .
                'class="sagepaysuite-signup">' .
                __('Get an Opayo Account Now!').'</a>' .
            '</strong>'.
            $this->getLineSubmitTicket() .
            $this->getLineFeedBack() .
            $this->getLineModuleVersion().
            '</div>
        </span>';

        $html .= '<div class="config-alt"></div>';
        $html .= '</div></div>';

        $html .= '<div id="paymentsuiteconfig-init" class="admin__page-section-item" style="display:none;"' .
            ' data-mage-init=\'{"Ebizmarts_SagePaySuite/js/paymentsuiteconfig":{' .
            '"configButtonId": "' . $element->getHtmlId() . '",'.
            '"configStateUrl": "' . $this->getUrl('adminhtml/*/state') . '"'.
            '}}\'>' .
            '</div>';

        return $html;
    }

    private function getLineSubmitTicket()
    {
        return '<p>'
            .__('Need assistance? <a href="mailto:sagepay@ebizmarts-desk.zendesk.com">Submit a ticket</a>')
            .'</p>';
    }

    private function getLineFeedBack()
    {
        return '
        <p>Got feedback?
            <a target="_blank" href="mailto:support@ebizmarts-desk.zendesk.com">'.__('Email us'). '</a> or
            <a target="_blank" href="https://store.ebizmarts.com/subscribe-to-our-newsletter">'.
            __('Subscribe to our newsletter'). '</a>
        </p>
        ';
    }

    private function getLineModuleVersion()
    {
        return '<p class="sagepaymoduleversion">' . $this->getModuleVersion() . '</p>';
    }

    private function getModuleVersion()
    {
        return $this->moduleVersion->getModuleVersion('Ebizmarts_SagePaySuite');
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getHeaderCommentHtml($element)
    {
        return '';
    }

    /**
     * Get collapsed state on-load
     *
     * @param AbstractElement $element
     * @return false
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _isCollapseState($element)
    {
        return false;
    }

    /**
     * @return array
     */
    private function getUrlParams(): array
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['website'])) {
            return ['website' => $params['website']];
        } elseif (isset($params['store'])) {
            return ['store' => $params['store']];
        }
        return [];
    }
}
