<?php

namespace Ebizmarts\BrippoPayments\Observer;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

class CheckoutSubmitFailed implements ObserverInterface
{
    protected $logger;

    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var OrderInterface $order */
            $order = $observer->getData('order');
            /** @var Exception $error */
            $ex = $observer->getData('error');

            /**
             * ISSUE: https://github.com/ebizmarts/brippo-payments/issues/336
             */
            if ($order != null && $ex->getMessage() == __('Too much point(s) used.')
                && !empty($order->getData('am_spent_reward_points'))) {
                $order->setData('am_spent_reward_points', 0);
                $this->logger->log(
                    "Remove am_rewards points from the order #" . $order->getIncrementId() ." before cancelling"
                );
            }
        } catch (Exception $ex) {
            $this->logger->log("Handle CheckoutSubmitFailed failed, error: " . $ex->getMessage());
            $this->logger->log($ex->getTraceAsString());
        }
    }
}
