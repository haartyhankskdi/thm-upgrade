<?php

namespace Ebizmarts\BrippoPayments\Helper\PaymentMethods;

use Ebizmarts\BrippoPayments\Exception\BrippoApiException;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Ebizmarts\BrippoPayments\Model\Config\Source\CurrencyMode;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentElementStandalone;
use Ebizmarts\BrippoPayments\Model\Express;
use Ebizmarts\BrippoPayments\Model\ExpressCheckoutElement;
use Ebizmarts\BrippoPayments\Model\PayByLink;
use Ebizmarts\BrippoPayments\Model\PayByLinkMoto;
use Ebizmarts\BrippoPayments\Model\PaymentElement;
use Ebizmarts\BrippoPayments\Model\TerminalBackend;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\Store;

class PaymentMethod extends AbstractHelper
{
    public $dataHelper;
    protected $json;
    protected $encryptor;

    /**
     * @param Context $context
     * @param DataHelper $dataHelper
     * @param Json $json
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        DataHelper $dataHelper,
        Json $json,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->json = $json;
        $this->encryptor = $encryptor;
    }

    public function isFrontendPaymentMethod($paymentMethodCode): bool
    {
        return $paymentMethodCode == Express::METHOD_CODE ||
            $paymentMethodCode == PaymentElement::METHOD_CODE ||
            $paymentMethodCode == PayByLink::METHOD_CODE ||
            $paymentMethodCode == PayByLinkMoto::METHOD_CODE ||
            $paymentMethodCode == TerminalBackend::METHOD_CODE ||
            $paymentMethodCode == PaymentElementStandalone::METHOD_CODE ||
            $paymentMethodCode == ExpressCheckoutElement::METHOD_CODE;
    }

    public function isPayByLink($paymentMethodCode): bool
    {
        return $paymentMethodCode == PayByLink::METHOD_CODE ||
            $paymentMethodCode == PayByLinkMoto::METHOD_CODE;
    }

    /**
     * @param $address
     * @return bool
     */
    public function isAddressValidForOrderSubmit($address): bool
    {
        return !empty($address)
            && (!empty($address->getFirstname()) && $address->getFirstname() !== '-')
            && (!empty($address->getLastname()) && $address->getLastname() !== '-')
            && (!empty($address->getStreet()) && $address->getStreet() !== '-')
            && (!empty($address->getPostcode()) && $address->getPostcode() !== '-')
            && (!empty($address->getCountryId()) && $address->getCountryId() !== '-')
            && (!empty($address->getTelephone() && strlen($address->getTelephone()) > 5));
    }

    /**
     * @param $quote
     * @param $billingAddress
     * @return void
     */
    public function setBillingAddressFromFrontendData($quote, $billingAddress): void
    {
        $addressData = [
            'firstname' => $billingAddress['firstname'],
            'lastname' => $billingAddress['lastname'],
            'street' => $billingAddress['street'],
            'city' => $billingAddress['city'],
            'country_id' => $billingAddress['countryId'],
            'postcode' => $billingAddress['postcode'],
            'telephone' => $billingAddress['telephone'] ?? 5555555555,
            'region' => $billingAddress['region'] ?? '',
            'region_id' => $billingAddress['regionId'] ?? 0
        ];

        $quote->getBillingAddress()->addData($addressData);
        $this->dataHelper->logger->log('Billing address was recovered from frontend.');
    }

    /**
     * @param $quote
     * @param $shippingAddress
     * @return void
     */
    public function setShippingAddressFromFrontendData($quote, $shippingAddress): void
    {
        if (!empty($shippingAddress['street'])
            && !empty($shippingAddress['city'])
            && !empty($shippingAddress['countryId'])
            && !empty($shippingAddress['postcode'])
            && !empty($shippingAddress['firstname'])
        ) {
            $addressData = [
                'firstname' => $shippingAddress['firstname'],
                'lastname' => $shippingAddress['lastname'],
                'street' => $shippingAddress['street'],
                'city' => $shippingAddress['city'],
                'country_id' => $shippingAddress['countryId'],
                'postcode' => $shippingAddress['postcode'],
                'telephone' => $shippingAddress['telephone'] ?? 5555555555,
                'region' => $shippingAddress['region'] ?? '',
                'region_id' => $shippingAddress['regionId'] ?? 0
            ];

            $quote->getShippingAddress()->addData($addressData);
            $this->dataHelper->logger->log('Shipping address was recovered from frontend.');
        }
    }

    /**
     * @param Quote $quote
     * @param int $scopeId
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getPaymentIntentCurrencyFromQuote(Quote $quote, int $scopeId): string
    {
        return $this->getPaymentIntentCurrency(
            $quote->getQuoteCurrencyCode(),
            $quote->getBaseCurrencyCode(),
            $quote->getStore(),
            $scopeId
        );
    }

    /**
     * @param OrderInterface $order
     * @param int $scopeId
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getPaymentIntentCurrencyFromOrder(OrderInterface $order, int $scopeId): string
    {
        return $this->getPaymentIntentCurrency(
            $order->getOrderCurrencyCode(),
            $order->getBaseCurrencyCode(),
            $order->getStore(),
            $scopeId
        );
    }

    /**
     * @param $currencyCode
     * @param $baseCurrency
     * @param Store $store
     * @param int $scopeId
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getPaymentIntentCurrency($currencyCode, $baseCurrency, Store $store, int $scopeId): string
    {
        $isConfiguredToUseBaseCurrency = $this->dataHelper->getStoreConfig(
            DataHelper::XML_PATH_CURRENCY_MODE,
            $scopeId
        ) === CurrencyMode::MODE_BASE_CURRENCY;

        $currency = $currencyCode;
        if ($isConfiguredToUseBaseCurrency) {
            $currency = $baseCurrency;
        }

        if (empty($currency)) {
            if ($isConfiguredToUseBaseCurrency) {
                $currency = $store->getBaseCurrency()->getCode();
            } else {
                $currency = $store->getCurrentCurrency()->getCode();
            }
        }

        if (empty($currency)) {
            throw new LocalizedException(__('Store currency is not configured'));
        }

        return $currency;
    }

    /**
     * @param OrderInterface $order
     * @param int $scopeId
     * @return float
     * @throws NoSuchEntityException
     */
    public function getPlaceOrderAmount(OrderInterface $order, int $scopeId): float
    {
        $isConfiguredToUseBaseCurrency = $this->dataHelper->getStoreConfig(
            DataHelper::XML_PATH_CURRENCY_MODE,
            $scopeId
        ) === CurrencyMode::MODE_BASE_CURRENCY;

        $amount = $order->getGrandTotal();
        if ($isConfiguredToUseBaseCurrency) {
            $amount = $order->getBaseGrandTotal();
        }

        return $amount;
    }

    /**
     * @param RequestInterface $request
     */
    public function setParamsFromRequestBody($request): void
    {
        if (!empty($request->getParam('hyva')) && !empty($request->getContent())) {
            try {
                $jsonVars = $this->json->unserialize($request->getContent());
                if (is_array($jsonVars) && !empty($jsonVars)) {
                    $request->setParams($jsonVars);
                }
            } catch (Exception $e) {
                $this->logger->log("Failed to unserialize, error: " . $e->getMessage());
            }
        }
    }

    /**
     * @param Session $session
     */
    public function generateOrderUniqId(Session $session): void
    {
        if (empty($session->getBrippoOrderUniqId())) {
            $session->setBrippoOrderUniqId(uniqid("op_"));
        }
    }

    /**
     * @param Session $session
     */
    public function resetBrippoOrderUniqId(Session $session): void
    {
        $session->setBrippoOrderUniqId(null);
    }

    /**
     * @param Quote $quote
     * @return void
     * @throws Exception
     */
    public function addValidationHashToQuote(Quote $quote): void
    {
        $hash = $this->encryptor->encrypt($this->createQuoteValidationHash($quote));
        $quote->setBrippoQuoteHash($hash);
        $quote->save();
    }

    /**
     * @param Quote $quote
     * @return bool
     */
    public function isQuoteHashValid(Quote $quote): bool
    {
        $validationStr = $this->createQuoteValidationHash($quote);
        $hash = (string)$quote->getBrippoQuoteHash();
        $decryptedValue = $this->encryptor->decrypt($hash);
        return $validationStr === $decryptedValue;
    }

    /**
     * @param Quote $quote
     * @return string
     */
    private function createQuoteValidationHash(Quote $quote): string
    {
        return $quote->getEntityId()
            . substr($this->dataHelper->getPlatformPublishableKey($quote->getStoreId()), 0, 20);
    }

    /**
     * @param $exception
     * @return string
     */
    public function getCancelStatusFromBrippoApiError($exception): string
    {
        if ($exception instanceof BrippoApiException) {
            if ($exception->statusCode === 406) {
                return BrippoOrder::STATUS_BLOCKED;
            }
        }
        return BrippoOrder::STATUS_GATEWAY_ERROR;
    }
}
