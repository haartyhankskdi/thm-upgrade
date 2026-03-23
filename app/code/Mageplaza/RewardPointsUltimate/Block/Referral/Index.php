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
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Block\Referral;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\RewardPoints\Helper\Email;
use Mageplaza\RewardPointsUltimate\Helper\Crypt;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\ReferralFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerActions;
use Mageplaza\RewardPointsUltimate\Model\Source\ReferralActions;

/**
 * Class Index
 * @package Mageplaza\RewardPointsUltimate\Block\Referral
 */
class Index extends Template
{
    /**
     * @var ReferralFactory
     */
    protected $referralFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Crypt
     */
    protected $crypt;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param ReferralFactory $referralFactory
     * @param Data $helperData
     * @param Crypt $crypt
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        ReferralFactory $referralFactory,
        Data $helperData,
        Crypt $crypt,
        Session $customerSession,
        array $data = []
    ) {
        $this->referralFactory = $referralFactory;
        $this->helperData = $helperData;
        $this->crypt = $crypt;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getRule()
    {
        $rule = $this->referralFactory->create()->getReferralRule();
        if (!$rule->getId()) {
            return false;
        }
        $pointHelper = $this->helperData->getPointHelper();
        if ($rule->getReferralType() == ReferralActions::TYPE_PRICE) {
            $this->setReferMessage(
                __(
                    'You will receive %1 for every %2 amount of price purchasing made by your friends.',
                    $pointHelper->format($rule->getReferralPoints(), false),
                    $rule->getReferralMoneyStep()
                )
            );
        } else {
            $this->setReferMessage(
                __(
                    'You will receive %1 purchasing made by your friends.',
                    $pointHelper->format(
                        $rule->getReferralPoints(),
                        false
                    )
                )
            );
        }

        $customerAction = $rule->getCustomerAction();
        switch ($customerAction) {
            case CustomerActions::TYPE_FIXED_POINTS:
                $this->setCustomerMessage(
                    __(
                        'Your friends will be received %1 when purchasing at our store.',
                        $pointHelper->format($rule->getCustomerPoints(), false)
                    )
                );
                break;
            case CustomerActions::TYPE_PRICE:
                $this->setCustomerMessage(__(
                    'Your friends will receive %1 for every %2 currency unit(s) when purchasing at our store',
                    $pointHelper->format($rule->getCustomerPoints(), false),
                    $rule->getCustomerMoneyStep()
                ));
                break;
            case CustomerActions::TYPE_FIXED_DISCOUNT:
                $this->setCustomerMessage(__(
                    'Your friends will receive %1 currency unit(s) when purchasing at our store',
                    $rule->getCustomerDiscount()
                ));
                break;
            case CustomerActions::TYPE_PERCENT:
                $this->setCustomerMessage(
                    __(
                        'Your friends will receive %1 percent discount on the order total when purchasing at our store',
                        $rule->getCustomerDiscount()
                    )
                );
                break;
        }

        return $rule;
    }

    /**
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->getCustomer()->getEmail();
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }

    /**
     * @return bool|string
     */
    public function getCode()
    {
        return $this->crypt->encrypt($this->getCustomer()->getId());
    }

    /**
     * @return string
     */
    public function getReferUrl()
    {
        return $this->helperData->getReferUrl($this->getCode());
    }

    /**
     * @return string
     */
    public function getSaveInvitationsUrl()
    {
        return $this->getUrl('customer/referral/send');
    }

    /**
     * @return Email
     */
    public function getEmailHelper()
    {
        return $this->helperData->getEmailHelper();
    }
}
