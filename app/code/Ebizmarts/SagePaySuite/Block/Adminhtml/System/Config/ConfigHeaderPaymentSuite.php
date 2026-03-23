<?php

namespace Ebizmarts\SagePaySuite\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Helper\Js;
use Ebizmarts\SagePaySuite\Model\Config\ModuleVersion;
use Magento\Framework\View\Asset\Repository as UrlFile;

class ConfigHeaderPaymentSuite extends Fieldset
{
    protected $isCollapsedDefault = false;

    /**
     * @var ModuleVersion
     */
    private $moduleVersion;

    /** @var UrlFile */
    private $urlFile;

    /**
     * @param Context $context
     * @param Session $authSession
     * @param Js $jsHelper
     * @param UrlFile $urlFile
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        ModuleVersion $moduleVersion,
        UrlFile $urlFile,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $authSession,
            $jsHelper,
            $data
        );
        $this->moduleVersion = $moduleVersion;
        $this->urlFile = $urlFile;
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
        <div class="ebizmartsPaymentSuite">
            <div class="textDescription">
                <h1>ebizmarts</h1><h2>Payment Suite <span class="number">10</span></h2>
            </div>
            <img src="https://brippo.s3.amazonaws.com/images/tarjeta-de-debito.png">
        </div>
        ';

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
}
