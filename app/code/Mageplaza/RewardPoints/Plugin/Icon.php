<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Plugin;

use Magento\Customer\Block\Account\Customer as IconCustomer;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPoints\Helper\Data as HelperData;

/**
 * Class Icon
 * @package Mageplaza\RewardPoints\Plugin
 */
class Icon
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Session
     */
    protected $_session;

    /**
     * Icon constructor.
     *
     * @param HelperData $helperData
     * @param StoreManagerInterface $storeManager
     * @param Session $session
     */
    public function __construct(
        HelperData $helperData,
        StoreManagerInterface $storeManager,
        Session $session
    ) {
        $this->helperData    = $helperData;
        $this->_storeManager = $storeManager;
        $this->_session      = $session;
    }

    /**
     * @param IconCustomer $subject
     * @param $result
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterToHtml(IconCustomer $subject, $result)
    {
        if ($this->helperData->checkHyvaTheme()
            && $this->helperData->isEnabled()
            && $this->helperData->isDisplayPointOnTopLink()
            && str_contains($result, 'id="customer-menu"')) {
            $icon = $this->addCustomerIconHtml();
            if ($icon) {
                return $result . $icon;
            }
        }

        return $result;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addCustomerIconHtml()
    {
        $html = '';
        if ($this->_session->isLoggedIn()) {
            $icon = '<div class="mp_header_icon"><a style="margin-top: 1%;" href="' . $this->_storeManager->getStore()->getUrl('customer/rewards/') . '">' .
                $this->helperData->getPointHelper()->getIconHtml()
                . '<span>' . $this->helperData->getAccountHelper()->get()->getBalanceFormatted() . '</span>'
                . '</a></div> <style>.mp_header_icon img{float:left;margin-top: 2px;margin-right: 4px;} </style>';
            $html = <<<SCRIPT
                        <script type="text/javascript">
                        document.getElementById('menu-search-icon').insertAdjacentHTML('beforebegin', '$icon');
                        </script>
                        SCRIPT;

        }

        return $html;
    }
}
