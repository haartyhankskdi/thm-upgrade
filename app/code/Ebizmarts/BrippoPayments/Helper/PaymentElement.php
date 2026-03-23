<?php

namespace Ebizmarts\BrippoPayments\Helper;

use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\PaymentMethods\PaymentMethod;
use Ebizmarts\BrippoPayments\Model\Config\Source\CurrencyMode;
use Ebizmarts\BrippoPayments\Model\Config\Source\ThreeDSecure;
use Ebizmarts\BrippoPayments\Model\Express;
use Ebizmarts\BrippoPayments\Model\PaymentElement as PaymentElementMethod;
use Exception;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote;

class PaymentElement extends PaymentMethod
{
    const PLACEMENT_ID_RECOVER_CHECKOUT = 'recover_checkout';
    const PLACEMENT_ID_CHECKOUT_STANDALONE = 'checkout_standalone';
    const PLACEMENT_ID_CHECKOUT = 'checkout';

    protected $stripeHelper;

    /**
     * @param Context $context
     * @param Data $dataHelper
     * @param Json $json
     * @param EncryptorInterface $encryptor
     * @param Stripe $stripeHelper
     */
    public function __construct(
        Context $context,
        DataHelper $dataHelper,
        Json $json,
        EncryptorInterface $encryptor,
        Stripe $stripeHelper
    ) {
        parent::__construct($context, $dataHelper, $json, $encryptor);
        $this->stripeHelper = $stripeHelper;
    }

    /**
     * @param Quote $quote
     * @param string $paymentMethodCode
     * @return Quote
     * @throws LocalizedException
     */
    public function fillMissingDataForPlaceOrder(
        Quote $quote,
        string $paymentMethodCode
    ): Quote {
        /**
         * Added to avoid Terms and conditions validation bug: https://github.com/magento/magento2/issues/31461
         */
        if ($quote->getPayment()->getMethod() != $paymentMethodCode) {
            $quote->setPaymentMethod($paymentMethodCode);
            $quote->getPayment()->importData(['method' => $paymentMethodCode]);
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals()->save();
        }

        return $quote;
    }

    /**
     * @param Quote $quote
     * @param int $scopeId
     * @return float
     * @throws NoSuchEntityException
     */
    public function getQuoteGrandTotal(Quote $quote, int $scopeId): float
    {
        $amount = $quote->getGrandTotal();

        if ($this->dataHelper->getStoreConfig(
            DataHelper::XML_PATH_CURRENCY_MODE,
            $scopeId
        ) === CurrencyMode::MODE_BASE_CURRENCY) {
            /*
             * Amend for currency mode
             */
            $amount = $quote->getBaseGrandTotal();
        }

        return $amount ?? 0;
    }

    public function setParamsFromRequestBody($request): void
    {
        if (!empty($request->getContent())) {
            try {
                $contentType = $request->getHeader('Content-Type');

                // Handle URL-encoded form data
                if ($contentType && strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
                    $this->dataHelper->logger->log('here 1');
                    parse_str($request->getContent(), $params);
                    if (is_array($params) && !empty($params)) {
                        $request->setParams($params);
                    }
                } else {
                    // Handle JSON data
                    $jsonVars = $this->json->unserialize($request->getContent());
                    if (is_array($jsonVars) && !empty($jsonVars)) {
                        $request->setParams($jsonVars);
                    }
                }
            } catch (Exception $e) {
                $this->dataHelper->logger->log("Failed to unserialize request body: " . $e->getMessage());
                try {
                    $this->dataHelper->logger->log('CT: ' . $request->getHeader('Content-Type'));
                    $this->dataHelper->logger->log('Method: ' . $request->getMethod());
                    $this->dataHelper->logger->log(print_r($request->getContent(), true));
                } catch (Exception $e) {
                    $this->dataHelper->logger->log("Failed to print request body: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * @param $scopeId
     * @param $order
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getThreeDSecure($scopeId, $order)
    {
        $threeDsConfig = $this->dataHelper->getStoreConfig(
            PaymentElementMethod::XML_PATH_THREE_D_SECURE,
            $scopeId
        );
        if ($threeDsConfig === ThreeDSecure::FORCE_FOR_THRESHOLD) {
            $threshold = $this->dataHelper->getStoreConfig(
                PaymentElementMethod::XML_PATH_THREE_D_SECURE_THRESHOLD,
                $scopeId
            );
            if (empty($threshold) || is_nan($threshold)) {
                $this->dataHelper->logger->log('Invalid 3D Secure threshold: ' . $threshold . '.');
                return ThreeDSecure::AUTOMATIC;
            } else {
                if ($order->getGrandTotal() >= $threshold) {
                    return ThreeDSecure::FORCE;
                } else {
                    return ThreeDSecure::AUTOMATIC;
                }
            }
        } else {
            return $threeDsConfig;
        }
    }

    /**
     * @param $scopeId
     * @param $isRecovery
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCaptureMethod($scopeId, $isRecovery): string
    {
        $captureMethod = $this->dataHelper->getStoreConfig(
            PaymentElementMethod::XML_PATH_CAPTURE_METHOD,
            $scopeId
        );
        if ($isRecovery) {
            $captureMethod = $this->dataHelper->getStoreConfig(
                Express::XML_PATH_CAPTURE_METHOD,
                $scopeId
            );
        }
        return $this->stripeHelper->getCaptureMethod($captureMethod);
    }
}
