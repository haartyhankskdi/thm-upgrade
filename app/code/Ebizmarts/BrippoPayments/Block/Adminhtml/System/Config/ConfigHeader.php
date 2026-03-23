<?php

namespace Ebizmarts\BrippoPayments\Block\Adminhtml\System\Config;

use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\PlatformService\PlatformService;
use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Helper\Js;
use Magento\Store\Model\ScopeInterface;

class ConfigHeader extends Fieldset
{
    protected $logger;
    protected $dataHelper;

    /**
     * @param Context $context
     * @param Session $authSession
     * @param Js $jsHelper
     * @param Logger $logger
     * @param DataHelper $dataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        Logger $logger,
        DataHelper $dataHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $authSession,
            $jsHelper,
            $data
        );
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
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
        $html = '<div class="config-heading" >';
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
            '<div class="brippo-payments-logo"><div class="logo"></div></div>' .
            '<div class="brippo-payments-text">' .
            '<strong>'.
            __('BRIPPO Payments (by <a target="_blank" href="https://ebizmarts.com">Ebizmarts</a>)').
            '</strong>'.
            '<p>'.__('This solution is powered by <i>Stripe Connect</i>.').'</p>'.
            '<p>'.__('Need support? <a href="mailto:brippo@ebizmarts-desk.zendesk.com">Submit a ticket</a>').'</p>' .
            '<p class="brippopayments version">' . $this->dataHelper->getExtensionsVersionString() . '</p>' .
            '</div></span>';

        $html .= '<div class="config-alt"></div>';
        $html .= '</div></div>';

        $html .= '<div id="brippoAdmin" class="admin__page-section-item" style="display:none;"' .
            ' data-mage-init=\'{"Ebizmarts_BrippoPayments/js/stripe-connect-admin":{' .
            '"scope": "' . $this->getScope() . '",' .
            '"scopeId": "' . $this->getScopeId() . '",' .
            '"logsUrl": "' . $this->getLogsUrl() . '",' .
            '"resetUrl": "' . $this->getResetUrl() . '",' .
            '"configButtonId": "' . $element->getHtmlId() . '",' .
            '"onboardingResponseUrl": "' . $this->getOnboardingResponseUrl() . '",' .
            '"onboardingGoToUrl": "' . $this->getOnboardingGoToUrl() . '",' .
            '"configStateUrl": "' . $this->getUrl('adminhtml/*/state') . '"}}\'>' .
            '</div>';

        return $html;
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
     * @return string
     */
    public function getOnboardingGoToUrl(): string
    {
        return PlatformService::SERVICE_URL . PlatformService::ENDPOINT_URI_ONBOARDING;
    }

    /**
     * @return string
     */
    public function getOnboardingResponseUrl(): string
    {
        $params = $this->getRequest()->getParams();
        $scope = 'default';
        $scopeId = 0;
        if (isset($params['website'])) {
            $scope = ScopeInterface::SCOPE_WEBSITES;
            $scopeId = $params['website'];
        } elseif (isset($params['store'])) {
            $scope = ScopeInterface::SCOPE_STORES;
            $scopeId = $params['store'];
        }

        return $this->_urlBuilder->getUrl(
            DataHelper::ONBOARDING_RESPONSE_URL,
            ['scope' => $scope, 'scopeId' => $scopeId]
        );
    }

    /**
     * @return string
     */
    public function getResetUrl(): string
    {
        return $this->_urlBuilder->getUrl(DataHelper::ONBOARDING_RESET_URL, $this->getUrlParams());
    }

    /**
     * @return string
     */
    public function getLogsUrl(): string
    {
        return $this->_urlBuilder->getUrl('brippo_payments/logs/download', $this->getUrlParams());
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

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->dataHelper->getScopeTypeFromUrl();
    }

    /**
     * @return int
     */
    public function getScopeId(): int
    {
        return $this->dataHelper->getScopeIdFromUrl();
    }
}
