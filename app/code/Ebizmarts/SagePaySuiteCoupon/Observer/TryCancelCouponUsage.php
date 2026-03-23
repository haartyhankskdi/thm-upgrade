<?php

namespace Ebizmarts\SagePaySuiteCoupon\Observer;

use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger as SagePayLogger;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\SalesRule\Api\Data\CouponInterfaceFactory;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\SalesRule\Model\Coupon\UpdateCouponUsages;
use Magento\SalesRule\Model\Coupon\UpdateCouponUsagesFactory;
use Magento\SalesRule\Api\CouponRepositoryInterfaceFactory;
use Magento\SalesRule\Api\CouponRepositoryInterface;

class TryCancelCouponUsage implements ObserverInterface
{
    private const SAGE_PAYMENT = 'sagepaysuite';
    public const IS_INCREMENT_USAGE_COUPON = false;

    /** @var SagePayLogger $suiteLogger */
    private $suiteLogger;

    /** @var CouponInterfaceFactory $couponInterfaceFactory */
    private $couponInterfaceFactory;

    /** @var UpdateCouponUsagesFactory $updateCouponUsagesFactory */
    private $updateCouponUsagesFactory;

    /** @var CouponRepositoryInterfaceFactory $couponRepositoryInterfaceFactory */
    private $couponRepositoryInterfaceFactory;

    /**
     * TryCancelCouponUsage Constructor
     *
     * @param CouponInterfaceFactory $couponInterfaceFactory
     * @param UpdateCouponUsagesFactory $updateCouponUsagesFactory
     * @param CouponRepositoryInterfaceFactory $couponRepositoryInterfaceFactory
     * @param SagePayLogger $suiteLogger
     */
    public function __construct(
        CouponInterfaceFactory $couponInterfaceFactory,
        UpdateCouponUsagesFactory $updateCouponUsagesFactory,
        CouponRepositoryInterfaceFactory $couponRepositoryInterfaceFactory,
        SagePayLogger $suiteLogger
    ) {
        $this->couponInterfaceFactory = $couponInterfaceFactory;
        $this->updateCouponUsagesFactory = $updateCouponUsagesFactory;
        $this->couponRepositoryInterfaceFactory = $couponRepositoryInterfaceFactory;
        $this->suiteLogger = $suiteLogger;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        /** @var OrderPaymentInterface $payment */
        $payment = $order->getPayment();
        if ($this->isSagePayment($payment)) {
            $couponCode = $order->getCouponCode();
            if ($this->isValidCouponCode($couponCode)) {
                $this->updateCoupon($couponCode);
                $this->updateCustomerCouponUsage($order);
            }
        }
    }

    /**
     * @param string $couponCode
     * @return bool
     */
    private function isValidCouponCode($couponCode)
    {
        return !empty($couponCode);
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return bool
     */
    private function isSagePayment($payment)
    {
        return $payment->getMethod() !== null && strpos($payment->getMethod(), self::SAGE_PAYMENT) !== false;
    }

    /**
     * @param string $couponCode
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function updateCoupon($couponCode)
    {
        try {
            /** @var CouponInterface $couponInterface */
            $couponInterface = $this->couponInterfaceFactory->create();
            $couponInterface = $couponInterface->loadByCode($couponCode);
            $couponInterface->setTimesUsed($couponInterface->getTimesUsed() - 1);
            /** @var CouponRepositoryInterface $couponRepositoryInterface */
            $couponRepositoryInterface = $this->couponRepositoryInterfaceFactory->create();
            $couponRepositoryInterface->save($couponInterface);
        } catch (\Exception $exception) {
            $this->suiteLogger->logException($exception, [__METHOD__, __LINE__]);
        }
    }

    /**
     * @param Order $order
     * @return void
     */
    private function updateCustomerCouponUsage($order)
    {
        try {
            /** @var UpdateCouponUsages $updateCouponUsages */
            $updateCouponUsages = $this->updateCouponUsagesFactory->create();
            $updateCouponUsages->execute($order, self::IS_INCREMENT_USAGE_COUPON);
        } catch (\Exception $exception) {
            $this->suiteLogger->logException($exception, [__METHOD__, __LINE__]);
        }
    }
}
