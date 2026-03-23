<?php

namespace Ebizmarts\SagePaySuiteCoupon\Observer;

use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CouponManagementInterfaceFactory;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Api\Data\CartInterface;

class RecoverCoupon implements ObserverInterface
{
    /** @var CouponManagementInterfaceFactory $couponManagementInterfaceFactory */
    private $couponManagementInterfaceFactory;

    /** @var CartRepositoryInterface */
    private $quoteRepository;

    /** @var Logger */
    private $suiteLogger;

    /**
     * @param CouponManagementInterfaceFactory $couponManagementInterfaceFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param Logger $suiteLogger
     */
    public function __construct(
        CouponManagementInterfaceFactory $couponManagementInterfaceFactory,
        CartRepositoryInterface $quoteRepository,
        Logger $suiteLogger
    ) {
        $this->couponManagementInterfaceFactory = $couponManagementInterfaceFactory;
        $this->quoteRepository = $quoteRepository;
        $this->suiteLogger = $suiteLogger;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var CartInterface $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var CartInterface $newQuote */
        $newQuote = $observer->getEvent()->getData('newQuote');
        if ($quote && $newQuote) {
            $couponCode = $quote->getCouponCode();
            if (!empty($couponCode)) {
                /** @var CouponManagementInterface $couponManagementInterface */
                $couponManagementInterface = $this->couponManagementInterfaceFactory->create();
                try {
                    $this->addCouponToQuote($couponManagementInterface, $newQuote, $couponCode);
                } catch (\Exception $exception) {
                    $this->suiteLogger->sageLog(
                        Logger::LOG_EXCEPTION,
                        'Could not replace coupon',
                        [__METHOD__, __LINE__]
                    );
                    $this->suiteLogger->sageLog(
                        Logger::LOG_EXCEPTION,
                        $exception->getMessage(),
                        [__METHOD__, __LINE__]
                    );
                    $this->suiteLogger->sageLog(
                        Logger::LOG_EXCEPTION,
                        $exception->getTraceAsString(),
                        [__METHOD__, __LINE__]
                    );
                }
            }
        }
    }

    /**
     * @param CouponManagementInterface $couponManagementInterface
     * @param CartInterface $newQuote
     * @param string $couponCode
     * @return void
     */
    private function addCouponToQuote($couponManagementInterface, $newQuote, $couponCode)
    {
        $couponManagementInterface->set($newQuote->getId(), $couponCode);
        $newQuote->setCouponCode($couponCode);
        $this->quoteRepository->save($newQuote);
    }
}
