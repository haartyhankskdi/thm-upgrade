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

namespace Mageplaza\RewardPointsUltimate\Plugin\Customer;

use Exception;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;
use Magento\SendFriend\Model\SendFriend;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;

/**
 * Class SendEmailToFriend
 * @package Mageplaza\RewardPointsUltimate\Plugin\Customer
 */
class SendEmailToFriend
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var BehaviorFactory
     */
    protected $behaviorFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * SendEmailToFriend constructor.
     *
     * @param HelperData $helperData
     * @param BehaviorFactory $behaviorFactory
     * @param Session $customerSession
     */
    public function __construct(
        HelperData $helperData,
        BehaviorFactory $behaviorFactory,
        Session $customerSession
    ) {
        $this->helperData = $helperData;
        $this->behaviorFactory = $behaviorFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * @param SendFriend $subject
     * @param callable $proceed
     *
     * @return mixed
     */
    public function aroundSend(SendFriend $subject, callable $proceed)
    {
        $customer = $this->customerSession->getCustomer();
        $customerId = $customer->getId();
        if ($this->helperData->isEnabled() && $customerId) {
            try {
                $result = $proceed();
                $pointSendEmailToFriends = $this->behaviorFactory->create()
                    ->getPointByAction(CustomerEvents::SEND_EMAIL_TO_FRIEND);
                if ($pointSendEmailToFriends > 0) {
                    $transaction = $this->helperData->getTransactionByFieldToFilter(
                        [
                            'action_code' => HelperData::ACTION_SEND_EMAIL_TO_FRIEND,
                            'customer_id' => $customerId
                        ],
                        false,
                        true
                    );

                    if (!$transaction->getData()) {
                        $this->helperData->getTransaction()->createTransaction(
                            HelperData::ACTION_SEND_EMAIL_TO_FRIEND,
                            $customer,
                            new DataObject(['point_amount' => $pointSendEmailToFriends])
                        );
                    }
                }
            } catch (Exception $e) {
                $result = $proceed();
            }
        } else {
            return $proceed();
        }

        return $result;
    }
}
