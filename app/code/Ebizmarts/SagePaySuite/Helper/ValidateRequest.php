<?php

namespace Ebizmarts\SagePaySuite\Helper;

use Ebizmarts\SagePaySuite\Api\Data\ValidateRequestValueInterface as ValidateRequestInterface;
use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

class ValidateRequest
{
    private const SAFE_REGEX = '/([^a-zA-Z\s\d+\'’‘ʻʼ\"\/\\\&:,.\-{}@])/';
    private const STREET_CITY_REGEX = '/[^à-źÀ-Źa-zA-Z0-9\s\d\-&,\'’‘ʻʼ.\/\\\:()+\\r\\n]/';
    private const POSTCODE_REGEX = '/[^a-zA-Z0-9\s\d\-]/';
    private const NAME_REGEX = '/[^à-źÀ-Źa-zA-Z0-9\'’‘ʻʼ\s\d\-.()\/\\\]/';
    private const MIDDLE_NAME_PREFIX_REGEX = '/[^a-zA-Z]/';
    private const SKU_REGEX = '/[^a-zA-Z0-9\s\d\-+]/';
    private const ITEM_DESCRIPTION_REGEX = '/[^a-zA-Z0-9à-źÀ-Ź\s\d\-&,\'’‘ʻʼ.:()+\/\\\]/';
    private const PHONE_REGEX = '/[^a-zA-Z0-9\s\d\-()+]/';
    private const SHIPPING_BILLING_POSTCODE_REGEX = '/[^a-zA-Z0-9 \-]/';
    private const SHIPPING_BILLING_NAME_REGEX = '/[^a-zA-Z0-9à-źÀ-Ź \-&,\'’‘ʻʼ.\/\\\]/';
    private const SHIPPING_BILLING_ADDRESS_REGEX = '/[^a-zA-Z0-9à-źÀ-Ź \-&,\'’‘ʻʼ.\/\\\:()+\\r\\n]/';
    private const SHIPPING_BILLING_ADDRESS_FORM_REGEX = '/[^a-zA-Z0-9à-źÀ-Ź \-&,\'’‘ʻʼ.\/\\\:()\\r\\n]/';
    private const SHIPPING_BILLING_PHONE_REGEX = '/[^a-zA-Z0-9 \-()+]/';
    private const PI_PHONE_REGEX = '/[^0-9 +\-()]/';
    public const SHIPPING_STREET = 'street';
    public const SHIPPING_CITY = 'city';
    public const SHIPPING_POSTCODE = 'postcode';
    public const SHIPPING_NAME = 'name';
    public const SHIPPING_MIDDLE_NAME = 'middleName';
    public const SKU = 'sku';
    public const ITEM_DESCRIPTION = 'itemDescription';
    public const SHIPPING_PREFIX = 'prefix';
    public const SHIPPING_PHONE = 'phone';
    private const ERROR_MESSAGE = '%1 field contains invalid characters.';

    /**
     * @param string $string
     * @param string|null $type
     * @return string
     */
    public function stringToSafeXMLChar($string, $type = null)
    {
        $safeRegex = self::SAFE_REGEX;
        $safeString = "";
        if ($type === self::SHIPPING_STREET || $type === self::SHIPPING_CITY) {
            $safeRegex = self::STREET_CITY_REGEX;
        } elseif ($type === self::SHIPPING_POSTCODE) {
            $safeRegex = self::POSTCODE_REGEX;
        } elseif ($type === self::SHIPPING_NAME) {
            $safeRegex = self::NAME_REGEX;
        } elseif ($type === self::SHIPPING_MIDDLE_NAME || $type === self::SHIPPING_PREFIX) {
            $safeRegex = self::MIDDLE_NAME_PREFIX_REGEX;
        } elseif ($type === self::SKU) {
            $safeRegex = self::SKU_REGEX;
        } elseif ($type === self::ITEM_DESCRIPTION) {
            $safeRegex = self::ITEM_DESCRIPTION_REGEX;
        } elseif ($type === self::SHIPPING_PHONE) {
            $safeRegex = self::PHONE_REGEX;
        }

        $length = strlen($string);
        for ($i = 0; $i < $length; $i++) {
            if (!$this->isValidFormat($safeRegex, $string, $i, 1)) {
                $safeString .= '';
            } else {
                $safeString .= substr($string, $i, 1);
            }
        }

        return $safeString;
    }

    /**
     * @param string $safeRegex
     * @param string $string
     * @param int $position
     * @param int $length
     * @return bool
     */
    private function isValidFormat($safeRegex, $string, $position, $length)
    {
        return empty(preg_match($safeRegex, substr($string, $position, $length)));
    }

    /**
     * @param Quote $quote
     * @return void
     * @throws LocalizedException
     */
    public function validateAddress($quote)
    {
        $paymentMethod = $quote->getPayment()->getMethod();
        /** @var Address $billingAddress */
        $billingAddress = $quote->getBillingAddress();
        /** @var Address $shippingAddress */
        $shippingAddress = $quote->isVirtual() ? $billingAddress : $quote->getShippingAddress();
        if ($paymentMethod === Config::METHOD_PAYPAL) {
            $this->isValidBillingAddress($billingAddress, $paymentMethod);
            $this->isValidShippingAddress($shippingAddress, $paymentMethod);
        } elseif ($paymentMethod === Config::METHOD_PI) {
            $this->isValidPiPhone($billingAddress);
        } elseif ($paymentMethod === Config::METHOD_FORM) {
            $this->isValidBillingAddress($billingAddress, $paymentMethod);
            $this->isValidShippingAddress($shippingAddress, $paymentMethod);
        }

        $this->isValidFormatAddress(
            self::SHIPPING_BILLING_POSTCODE_REGEX,
            $billingAddress->getPostcode(),
            ValidateRequestInterface::STRING_LENGTH_TWENTY,
            'A Postcode'
        );

        $this->isValidFormatAddress(
            self::SHIPPING_BILLING_POSTCODE_REGEX,
            $shippingAddress->getPostcode(),
            ValidateRequestInterface::STRING_LENGTH_TWENTY,
            'A Postcode'
        );
    }

    /**
     * @param Address $billingAddress
     * @return void
     * @throws LocalizedException
     */
    private function isValidPiPhone($billingAddress)
    {
        if (!empty($billingAddress->getTelephone())) {
            $billingPhone = substr(
                trim($billingAddress->getTelephone()),
                ValidateRequestInterface::OFFSET_TRIM_ZERO,
                ValidateRequestInterface::STRING_LENGTH_NINETEEN
            );
            $isValidBillingPhone = preg_match_all(self::PI_PHONE_REGEX, $billingPhone);
            if (!empty($isValidBillingPhone)) {
                throw new LocalizedException(__('A Phone field contains invalid characters.'));
            }
        }
    }

    /**
     * @param Address $quoteBillingAddress
     * @param string $paymentMethod
     * @return void
     * @throws LocalizedException
     */
    private function isValidBillingAddress($quoteBillingAddress, $paymentMethod)
    {
        $this->isValidFormatAddress(
            self::NAME_REGEX,
            $quoteBillingAddress->getLastname(),
            ValidateRequestInterface::STRING_LENGTH_TWENTY,
            'Surname'
        );

        $this->isValidFormatAddress(
            self::NAME_REGEX,
            $quoteBillingAddress->getFirstname(),
            ValidateRequestInterface::STRING_LENGTH_TWENTY,
            'Firstname'
        );

        $addressRegex = $paymentMethod === Config::METHOD_PAYPAL
            ? self::SHIPPING_BILLING_ADDRESS_REGEX
            : self::SHIPPING_BILLING_ADDRESS_FORM_REGEX;
        $this->isValidFormatAddress(
            $addressRegex,
            $quoteBillingAddress->getStreetLine(1),
            ValidateRequestInterface::ADDRESS_LENGTH,
            'Billing address'
        );

        $this->isValidFormatAddress(
            $addressRegex,
            $quoteBillingAddress->getStreetLine(2),
            ValidateRequestInterface::ADDRESS_LENGTH,
            'Billing address line two'
        );

        $this->isValidFormatAddress(
            $addressRegex,
            $quoteBillingAddress->getCity(),
            ValidateRequestInterface::STRING_LENGTH_FORTY,
            'Billing city'
        );

        $this->isValidFormatAddress(
            self::SHIPPING_BILLING_PHONE_REGEX,
            $quoteBillingAddress->getTelephone(),
            ValidateRequestInterface::STRING_LENGTH_NINETEEN,
            'Billing phone'
        );
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $quoteShippingAddress
     * @param string $paymentMethod
     * @return void
     * @throws LocalizedException
     */
    private function isValidShippingAddress($quoteShippingAddress, $paymentMethod)
    {
        $this->isValidFormatAddress(
            self::SHIPPING_BILLING_NAME_REGEX,
            $quoteShippingAddress->getLastname(),
            ValidateRequestInterface::STRING_LENGTH_TWENTY,
            'Surname'
        );

        $this->isValidFormatAddress(
            self::SHIPPING_BILLING_NAME_REGEX,
            $quoteShippingAddress->getFirstname(),
            ValidateRequestInterface::STRING_LENGTH_TWENTY,
            'Firstname'
        );

        $addressRegex = $paymentMethod === Config::METHOD_PAYPAL
            ? self::SHIPPING_BILLING_ADDRESS_REGEX
            : self::SHIPPING_BILLING_ADDRESS_FORM_REGEX;
        $this->isValidFormatAddress(
            $addressRegex,
            $quoteShippingAddress->getStreetLine(1),
            ValidateRequestInterface::ADDRESS_LENGTH,
            'Shipping address'
        );

        $this->isValidFormatAddress(
            $addressRegex,
            $quoteShippingAddress->getStreetLine(2),
            ValidateRequestInterface::ADDRESS_LENGTH,
            'Shipping address line 2'
        );

        $this->isValidFormatAddress(
            $addressRegex,
            $quoteShippingAddress->getCity(),
            ValidateRequestInterface::STRING_LENGTH_FORTY,
            'Shipping city'
        );

        $this->isValidFormatAddress(
            self::SHIPPING_BILLING_PHONE_REGEX,
            $quoteShippingAddress->getTelephone(),
            ValidateRequestInterface::STRING_LENGTH_NINETEEN,
            'Shipping phone'
        );
    }

    /**
     * @param string $regex
     * @param string $string
     * @param int $lenght
     * @param string $fieldError
     * @return void
     */
    private function isValidFormatAddress($regex, $string, $lenght, $fieldError)
    {
        $string = $this->isValidString($string)
            ? substr($string, ValidateRequestInterface::OFFSET_TRIM_ZERO, $lenght)
            : '';
        $isValidString = preg_match_all($regex, $string);
        if (!empty($isValidString)) {
            throw new LocalizedException(__($this->getErrorMessage(), $fieldError));
        }
    }

    /**
     * @param string $string
     * @return bool
     */
    private function isValidString($string)
    {
        return !empty($string);
    }

    private function getErrorMessage()
    {
        return self::ERROR_MESSAGE;
    }
}
