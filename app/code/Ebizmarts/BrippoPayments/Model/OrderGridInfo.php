<?php

namespace Ebizmarts\BrippoPayments\Model;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents as BrippoPaymentIntentsApi;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Payments as PaymentsHelper;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;
use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderGridInfo
{
    const MAX_REQUESTS_PER_PAGE = 5;
    public const FRAUD_STATE_NOT_AVAILABLE = 'NOT_AVAILABLE';
    public const FRAUD_STATE_RELOAD_REQUIRED = 'RELOAD_REQUIRED';

    /**
     * @var RequestInterface
     */
    protected $requestInterface;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var PaymentsHelper
     */
    private $paymentsHelper;

    /**
     * @var Stripe
     */
    private $stripeHelper;

    /**
     * @var BrippoPaymentIntentsApi
     */
    private $brippoApiPaymentIntents;

    /**
     * @param RequestInterface $requestInterface
     * @param OrderRepositoryInterface $orderRepository
     * @param Logger $logger
     * @param PaymentsHelper $paymentsHelper
     * @param Stripe $stripeHelper
     * @param BrippoPaymentIntentsApi $brippoApiPaymentIntents
     */
    public function __construct(
        RequestInterface $requestInterface,
        OrderRepositoryInterface $orderRepository,
        Logger $logger,
        PaymentsHelper $paymentsHelper,
        Stripe $stripeHelper,
        BrippoPaymentIntentsApi $brippoApiPaymentIntents
    ) {
        $this->requestInterface = $requestInterface;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->paymentsHelper = $paymentsHelper;
        $this->stripeHelper = $stripeHelper;
        $this->brippoApiPaymentIntents = $brippoApiPaymentIntents;
    }

    /**
     * @param array $dataSource
     * @param string $index
     * @param string $fieldName
     * @return array
     */
    public function prepareColumn(array $dataSource, string $index, string $fieldName) :array
    {
        try {
            if (!isset($dataSource['data']['items'])) {
                return $dataSource;
            }

            $paymentServiceRequests = 0;
            foreach ($dataSource['data']['items'] as &$item) {
                if (empty($item['payment_method'])) {
                    continue;
                }

                if (strpos($item['payment_method'], "brippo") === false) {
                    continue;
                }

                try {
                    $order = $this->orderRepository->get($item['entity_id']);
                } catch (Exception $e) {
                    continue;
                }

                $payment = $order->getPayment();
                if (empty($payment)) {
                    continue;
                }

                $additionalInformation = $payment->getAdditionalInformation();
                if (empty($additionalInformation) || !is_array($additionalInformation)) {
                    continue;
                }

                $specialFlag = null;
                $checkedPaymentIntent = false;

                /*
                 * IF INFO NOT SAVED LOAD FROM PAYMENT INTENT
                 */
                if (empty($additionalInformation[$index])) {
                    if ($paymentServiceRequests >= self::MAX_REQUESTS_PER_PAGE) {
                        $specialFlag = self::FRAUD_STATE_RELOAD_REQUIRED;
                    } elseif (!empty($payment->getAdditionalInformation(
                        PaymentMethod::ADDITIONAL_DATA_FRAUD_NOT_AVAILABLE
                    ))
                        || !empty($payment->getAdditionalInformation(
                            PaymentMethod::ADDITIONAL_DATA_FAILED
                        ))
                    ) {
                        $specialFlag = self::FRAUD_STATE_NOT_AVAILABLE;
                    } else {
                        try {
                            ++$paymentServiceRequests;
                            $paymentUpdated = $this->loadAdditionalFraudDataFromPaymentIntent($payment);
                            $checkedPaymentIntent = true;
                        } catch (Exception $ex) {
                            $paymentUpdated = $payment;
                        }

                        if (!empty($paymentUpdated->getAdditionalInformation())) {
                            $additionalInformation = $paymentUpdated->getAdditionalInformation();
                        }
                    }
                }

                /*
                 * MARK AS NOT AVAILABLE
                 */
                if ($checkedPaymentIntent
                    && empty($additionalInformation[$index])
                    && empty($payment->getAdditionalInformation(
                        PaymentMethod::ADDITIONAL_DATA_FRAUD_NOT_AVAILABLE
                    ))) {
                    $payment->setAdditionalInformation(
                        PaymentMethod::ADDITIONAL_DATA_FRAUD_NOT_AVAILABLE,
                        true
                    )->save();
                    $specialFlag = self::FRAUD_STATE_NOT_AVAILABLE;
                }

                $item[$fieldName] = $this->getIconClass($additionalInformation, $specialFlag ?? $index);
            }
        } catch (Exception $ex) {
            return $dataSource;
        }

        return $dataSource;
    }

    /**
     * @param $payment
     * @return mixed
     * @throws LocalizedException
     * @throws Exception
     */
    private function loadAdditionalFraudDataFromPaymentIntent($payment)
    {
        $paymentIntentId = $payment->getAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_PAYMENT_INTENT_ID
        );
        $liveMode = $payment->getAdditionalInformation(
            PaymentMethod::ADDITIONAL_DATA_LIVEMODE
        );

        if (!empty($paymentIntentId)) {
            $paymentIntent = $this->brippoApiPaymentIntents->get($paymentIntentId, $liveMode ?? true);
            if (!isset($paymentIntent[Stripe::PARAM_LATEST_CHARGE])) {
                if (isset($paymentIntent[Stripe::PARAM_LAST_PAYMENT_ERROR])) {
                    $this->paymentsHelper->savePayment3DSDetails($payment, $paymentIntent);
                    return $payment;
                } else {
                    throw new LocalizedException(__('No charge found'));
                }
            }

            if (!isset($payment->getAdditionalInformation()[Stripe::METADATA_KEY_RADAR_RISK])) {
                $this->paymentsHelper->savePayment3DSDetails($payment, $paymentIntent);
                $this->paymentsHelper->savePaymentFraudDetails($payment, $paymentIntent);

                $destinationChargeId = $this->stripeHelper->getTransferChargeIdFromPaymentIntent(
                    $paymentIntent
                );
                if (!empty($destinationChargeId)) {
                    $payment->setAdditionalInformation(
                        PaymentMethod::ADDITIONAL_DATA_TRANSFER_CHARGE_ID,
                        $destinationChargeId
                    )->save();
                }
            }
        }
        return $payment;
    }
}
