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

namespace Mageplaza\RewardPointsUltimate\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Model\InvitationFactory;

/**
 * Class QuoteSubmitSuccess
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class QuoteSubmitSuccess implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var InvitationFactory
     */
    protected $invitationFactory;

    /**
     * QuoteSubmitSuccess constructor.
     *
     * @param HelperData $helperData
     * @param Session $customerSession
     * @param InvitationFactory $invitationFactory
     */
    public function __construct(
        HelperData $helperData,
        Session $customerSession,
        InvitationFactory $invitationFactory
    ) {
        $this->helperData        = $helperData;
        $this->customerSession   = $customerSession;
        $this->invitationFactory = $invitationFactory;
    }

    /**
     * @param EventObserver $observer
     *
     * @throws InputException
     * @throws LocalizedException
     * @throws FailureToSendException
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        /**
         * Save invited to history and delete refer cookie
         */
        if ($quote->getMpRewardReferralEarn()) {
            $invitation    = $this->invitationFactory->create();
            $referralId    = $quote->getMpRewardReferralId();
            $referral      = $this->helperData->getAccountHelper()
                ->getCustomerById($referralId);
            $referralEmail = $referral->getEmail();
            $invitation->setReferralId($referralId)
                ->setReferralEmail($referralEmail)
                ->setReferralEarn($quote->getMpRewardReferralEarn())
                ->setInvitedEmail($quote->getCustomerEmail())
                ->setInvitedEarn($quote->getInvitedEarn())
                ->setInvitedDiscount($quote->getMpRewardInvitedDiscount())
                ->setStoreId($quote->getStoreId())
                ->save();

            /**
             * Delete refer cookie
             */
            $this->helperData->getCookieHelper()->deleteMpRefererKeyFromCookie();
        }

        /**
         * Save product id purchased and create transaction sell point
         */
        if ($quote->getCustomerId() && $quote->getItems()) {
            $mpRewardSellPoints = 0;
            foreach ($quote->getItems() as $item) {
                if ($item->getMpRewardSellPoints()) {
                    $mpRewardSellPoints += ($item->getMpRewardSellPoints() * $item->getQty());
                }
            }

            if ($mpRewardSellPoints > 0) {
                $this->helperData->addTransaction(
                    HelperData::ACTION_SELL_POINTS,
                    $quote->getCustomer(),
                    -$mpRewardSellPoints,
                    $order
                );
            }
        }
    }
}
