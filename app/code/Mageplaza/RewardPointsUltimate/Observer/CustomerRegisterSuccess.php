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

use Exception;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;

/**
 * Class CustomerRegisterSuccess
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class CustomerRegisterSuccess implements ObserverInterface
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
     * CustomerRegisterSuccess constructor.
     *
     * @param HelperData $helperData
     * @param BehaviorFactory $behaviorFactory
     */
    public function __construct(
        HelperData $helperData,
        BehaviorFactory $behaviorFactory
    ) {
        $this->helperData      = $helperData;
        $this->behaviorFactory = $behaviorFactory;
    }

    /**
     * @param EventObserver $observer
     *
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        if ($this->helperData->isEnabled()) {
            $this->behaviorSignUp($observer->getEvent()->getCustomer());
            $this->behaviorNewLetter($observer);
            $this->setCookieReferer($observer);
            $this->helperData->updateTier($observer->getEvent()->getCustomer()->getId());
        }
    }

    /**
     * @param Object $customer
     *
     * @throws LocalizedException
     */
    public function behaviorSignUp($customer)
    {
        $behavior = $this->behaviorFactory->create()->getBehaviorRuleByAction(CustomerEvents::SIGN_UP);
        if ($behavior->getRuleId()) {
            $pointSignUp = $behavior->getPointAmount();
            $expireAfter = $behavior->getExpireAfter();
            if ($this->isAllowCustomerGroup($customer->getGroupId(),
                    $behavior->getCustomerGroupIds()) && $pointSignUp) {
                $this->helperData->getTransaction()->createTransaction(
                    HelperData::ACTION_SIGN_UP,
                    $customer,
                    new DataObject([
                        'point_amount' => $pointSignUp,
                        'expireAfter'  => $expireAfter
                    ])
                );
            }
        }
    }

    /**
     * @param EventObserver $observer
     *
     * @throws LocalizedException
     */
    public function behaviorNewLetter($observer)
    {
        $customer          = $observer->getEvent()->getCustomer();
        $accountController = $observer->getEvent()->getAccountController();
        $pointSubscriber   = $this->behaviorFactory->create()->getPointByAction(CustomerEvents::NEWSLETTER);
        $expireAfter       = $this->behaviorFactory->create()->getExpireAfterByAction(CustomerEvents::NEWSLETTER);

        if ($accountController->getRequest()->getParam('is_subscribed') && $pointSubscriber) {
            $this->helperData->getTransaction()->createTransaction(
                HelperData::ACTION_NEWSLETTER,
                $customer,
                new DataObject([
                    'point_amount' => $pointSubscriber,
                    'expireAfter'  => $expireAfter
                ])
            );
        }
    }

    /**
     * @param EventObserver $observer
     *
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     * @throws InputException
     */
    public function setCookieReferer($observer)
    {
        $accountController = $observer->getEvent()->getAccountController();
        // $referCodeOrEmail  = trim($accountController->getRequest()->getParam('mp_refer'));
        $referCodeOrEmail = trim($accountController->getRequest()->getParam('mp_refer') ?? '');


        try {
            $referCode = $this->helperData->getCryptHelper()->checkReferCodeOrEmail($referCodeOrEmail);
        } catch (Exception $e) {
            $referCode = false;
        }

        if ($referCode) {
            $this->helperData->getCookieHelper()->set($referCode);
        }
    }

    /**
     * @param int $customerGroupId
     * @param array $customerGroupIds
     *
     * @return bool
     */
    public function isAllowCustomerGroup($customerGroupId, $customerGroupIds)
    {
        if (in_array($customerGroupId, $customerGroupIds ?: [])) {
            return true;
        }

        return false;
    }
}
