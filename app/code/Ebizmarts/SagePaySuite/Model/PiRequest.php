<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite\Helper\Request;
use Ebizmarts\SagePaySuite\Helper\ValidateRequest;
use Ebizmarts\SagePaySuite\Api\Data\ValidateRequestValueInterface as ValidateRequestInterface;

class PiRequest
{
    /** @var \Magento\Quote\Model\Quote */
    private $cart;

    /** @var  Request */
    private $requestHelper;

    /** @var Config */
    private $sagepayConfig;

    /** @var string The merchant session key used to generate the cardIdentifier. */
    private $merchantSessionKey;

    /** @var string The unique reference of the card you want to charge. */
    private $cardIdentifier;

    /** @var string Your unique reference for this transaction. Maximum of 40 characters. */
    private $vendorTxCode;

    /** @var bool */
    private $isMoto;

    /** @var \Ebizmarts\SagePaySuite\Api\Data\PiRequest */
    private $requestInfo;

    /** @var ValidateRequest */
    private $validateRequest;

    public function __construct(
        Request $requestHelper,
        Config $sagepayConfig,
        ValidateRequest $validateRequest
    ) {
        $this->requestHelper = $requestHelper;
        $this->sagepayConfig = $sagepayConfig;
        $this->validateRequest = $validateRequest;
    }

    /**
     * @return array
     */
    public function getRequestData()
    {
        $billingAddress  = $this->getCart()->getBillingAddress();
        $shippingAddress = $this->getCart()->getIsVirtual() ? $billingAddress : $this->getCart()->getShippingAddress();
        $this->validateRequest->validateAddress($this->getCart());
        $billingPhone = !empty($billingAddress->getTelephone())
            ? substr(
                trim($billingAddress->getTelephone()),
                ValidateRequestInterface::OFFSET_TRIM_ZERO,
                ValidateRequestInterface::STRING_LENGTH_NINETEEN
            ) : '';

        $data = [
            ValidateRequestInterface::TRANSACTION_TYPE => $this->sagepayConfig->getSagepayPaymentAction(),
            ValidateRequestInterface::PAYMENT_METHOD   => [
                ValidateRequestInterface::CARD => [
                    ValidateRequestInterface::MERCHANT_SESSION_KEY => $this->getMerchantSessionKey(),
                    ValidateRequestInterface::CARD_IDENTIFIER      => $this->getCardIdentifier(),
                    ValidateRequestInterface::SAVE_TOKEN           => $this->getSaveToken(),
                    ValidateRequestInterface::REUSABLE_TOKEN       => $this->getReusableToken()
                ]
            ],
            ValidateRequestInterface::VENDOR_TX_CODE => $this->getVendorTxCode(),
            ValidateRequestInterface::ORDER_DESCRIPTION => $this->requestHelper->getOrderDescription(
                $this->getIsMoto()
            ),
            ValidateRequestInterface::CUSTOMER_FIRST_NAME => substr(
                trim($billingAddress->getFirstname()),
                ValidateRequestInterface::OFFSET_TRIM_ZERO,
                ValidateRequestInterface::STRING_LENGTH_TWENTY
            ),
            ValidateRequestInterface::CUSTOMER_LAST_NAME => substr(
                trim($billingAddress->getLastname()),
                ValidateRequestInterface::OFFSET_TRIM_ZERO,
                ValidateRequestInterface::STRING_LENGTH_TWENTY
            ),
            ValidateRequestInterface::AVS_CVC_CHECK => $this->sagepayConfig->getAvsCvc(),
            ValidateRequestInterface::REFERRED_ID => $this->requestHelper->getReferrerId(),
            ValidateRequestInterface::CUSTOMER_EMAIL => $billingAddress->getEmail(),
            ValidateRequestInterface::CUSTOMER_PHONE => $billingPhone,
        ];

        if ($this->getIsMoto()) {
            $data[ValidateRequestInterface::ENTRY_METHOD] = ValidateRequestInterface::ENTRY_METHOD_TELEPHONE_ORDER;
        } else {
            $data[ValidateRequestInterface::ENTRY_METHOD] = ValidateRequestInterface::ENTRY_METHOD_ECOMMERCE;
            $data[ValidateRequestInterface::APPLY_3D_SECURE] = $this->sagepayConfig->get3Dsecure();
        }

        $data[ValidateRequestInterface::BILLING_ADDRESS] = [
            ValidateRequestInterface::ADDRESS_1 => substr(
                trim($billingAddress->getStreetLine(ValidateRequestInterface::START_POSITION_TRIM)),
                ValidateRequestInterface::OFFSET_TRIM_ZERO,
                ValidateRequestInterface::ADDRESS_LENGTH
            ),
            ValidateRequestInterface::BILLING_CITY => substr(
                trim($billingAddress->getCity()),
                ValidateRequestInterface::OFFSET_TRIM_ZERO,
                ValidateRequestInterface::STRING_LENGTH_FORTY
            ),
            ValidateRequestInterface::POSTAL_CODE => substr(
                trim($this->sanitizePostcode($billingAddress->getPostcode())),
                ValidateRequestInterface::OFFSET_TRIM_ZERO,
                ValidateRequestInterface::STRING_LENGTH_TEN
            ),
            ValidateRequestInterface::COUNTRY => substr(
                trim($billingAddress->getCountryId()),
                ValidateRequestInterface::OFFSET_TRIM_ZERO,
                ValidateRequestInterface::STRING_LENGTH_TWO
            )
        ];
        if ($this->isValidCountry(
            $data[ValidateRequestInterface::BILLING_ADDRESS],
            ValidateRequestInterface::COUNTRY_US
        )) {
            $data[ValidateRequestInterface::BILLING_ADDRESS][ValidateRequestInterface::STATE] = substr(
                $billingAddress->getRegionCode(),
                ValidateRequestInterface::OFFSET_TRIM_ZERO,
                ValidateRequestInterface::STRING_LENGTH_TWO
            );
        } else {
            if ($this->isValidCountryAndEmpty(
                $data[ValidateRequestInterface::BILLING_ADDRESS],
                ValidateRequestInterface::COUNTRY_IE
            )) {
                $data[ValidateRequestInterface::BILLING_ADDRESS][ValidateRequestInterface::POSTAL_CODE] =
                    ValidateRequestInterface::UNASSIGNED_POSTAL_CODE;
            } else {
                if ($this->isValidCountryAndEmpty(
                    $data[ValidateRequestInterface::BILLING_ADDRESS],
                    ValidateRequestInterface::COUNTRY_HK
                )) {
                    $data[ValidateRequestInterface::BILLING_ADDRESS][ValidateRequestInterface::POSTAL_CODE] =
                        ValidateRequestInterface::UNASSIGNED_POSTAL_CODE;
                }
            }
        }

        $data[ValidateRequestInterface::SHIPPING_DETAILS] = [
            ValidateRequestInterface::RECIPIENT_FIRST_NAME => substr(
                trim($shippingAddress->getFirstname()),
                ValidateRequestInterface::OFFSET_TRIM_ZERO,
                ValidateRequestInterface::STRING_LENGTH_TWENTY
            ),
            ValidateRequestInterface::RECIPIENT_LAST_NAME => substr(
                trim($shippingAddress->getLastname()),
                ValidateRequestInterface::OFFSET_TRIM_ZERO,
                ValidateRequestInterface::STRING_LENGTH_TWENTY
            ),
            ValidateRequestInterface::SHIPPING_ADDRESS_1 => substr(
                trim($shippingAddress->getStreetLine(ValidateRequestInterface::START_POSITION_TRIM)),
                ValidateRequestInterface::OFFSET_TRIM_ZERO,
                ValidateRequestInterface::ADDRESS_LENGTH
            ),
            ValidateRequestInterface::SHIPPING_CITY => substr(
                trim($shippingAddress->getCity()),
                ValidateRequestInterface::OFFSET_TRIM_ZERO,
                ValidateRequestInterface::STRING_LENGTH_FORTY
            ),
            ValidateRequestInterface::SHIPPING_POSTAL_CODE => substr(
                trim($this->sanitizePostcode($shippingAddress->getPostcode())),
                ValidateRequestInterface::OFFSET_TRIM_ZERO,
                ValidateRequestInterface::STRING_LENGTH_TEN
            ),
            ValidateRequestInterface::SHIPPING_COUNTRY => substr(
                trim($shippingAddress->getCountryId()),
                ValidateRequestInterface::OFFSET_TRIM_ZERO,
                ValidateRequestInterface::STRING_LENGTH_TWO
            )
        ];
        if ($this->isValidCountry(
            $data[ValidateRequestInterface::SHIPPING_DETAILS],
            ValidateRequestInterface::COUNTRY_US,
            true
        )) {
            $data[ValidateRequestInterface::SHIPPING_DETAILS][ValidateRequestInterface::SHIPPING_STATE] =
                substr(
                    $shippingAddress->getRegionCode(),
                    ValidateRequestInterface::OFFSET_TRIM_ZERO,
                    ValidateRequestInterface::STRING_LENGTH_TWO
                );
        } else {
            if ($this->isValidCountryAndEmpty(
                $data[ValidateRequestInterface::SHIPPING_DETAILS],
                ValidateRequestInterface::COUNTRY_IE,
                true
            )) {
                $data[ValidateRequestInterface::SHIPPING_DETAILS][ValidateRequestInterface::SHIPPING_POSTAL_CODE] =
                    ValidateRequestInterface::UNASSIGNED_POSTAL_CODE;
            } else {
                if ($this->isValidCountryAndEmpty(
                    $data[ValidateRequestInterface::SHIPPING_DETAILS],
                    ValidateRequestInterface::COUNTRY_HK,
                    true
                )) {
                    $data[ValidateRequestInterface::SHIPPING_DETAILS][ValidateRequestInterface::SHIPPING_POSTAL_CODE] =
                        ValidateRequestInterface::UNASSIGNED_POSTAL_CODE;
                }
            }
        }

        //populate payment amount information
        $data = array_merge($data, $this->requestHelper->populatePaymentAmountAndCurrency($this->getCart(), true));

        return $data;
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Api\Data\PiRequest $data
     * @return $this
     */
    public function setRequest(\Ebizmarts\SagePaySuite\Api\Data\PiRequest $data)
    {
        $this->requestInfo = $data;
        return $this;
    }

    public function getRequest()
    {
        return $this->requestInfo;
    }

    /**
     * @return string
     */
    public function getMerchantSessionKey()
    {
        return $this->merchantSessionKey;
    }

    /**
     * @param string $merchantSessionKey
     * @return \Ebizmarts\SagePaySuite\Model\PiRequest
     */
    public function setMerchantSessionKey($merchantSessionKey)
    {
        $this->merchantSessionKey = $merchantSessionKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getCardIdentifier()
    {
        return $this->cardIdentifier;
    }

    /**
     * @param string $cardIdentifier
     * @return \Ebizmarts\SagePaySuite\Model\PiRequest
     */
    public function setCardIdentifier($cardIdentifier)
    {
        $this->cardIdentifier = $cardIdentifier;
        return $this;
    }

    /**
     * @param string $vendorTxCode
     * @return \Ebizmarts\SagePaySuite\Model\PiRequest
     */
    public function setVendorTxCode($vendorTxCode)
    {
        $this->vendorTxCode = $vendorTxCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getVendorTxCode()
    {
        return $this->vendorTxCode;
    }

    /**
     * @return bool
     */
    public function getIsMoto()
    {
        return $this->isMoto;
    }

    /**
     * @param bool $isMoto
     * @return \Ebizmarts\SagePaySuite\Model\PiRequest
     */
    public function setIsMoto($isMoto)
    {
        $this->isMoto = $isMoto;
        return $this;
    }

    /**
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @return \Ebizmarts\SagePaySuite\Model\PiRequest
     */
    public function setCart(\Magento\Quote\Api\Data\CartInterface $cart)
    {
        $this->cart = $cart;
        return $this;
    }

    /**
     * @param $text
     * @return string
     */
    private function sanitizePostcode($postCode)
    {
        return !empty($postCode) ? preg_replace("/[^a-zA-Z0-9-\s]/", "", $postCode) : '000';
    }

    /**
     * @return bool
     */
    public function getSaveToken()
    {
        return $this->getRequest()->getSaveToken();
    }

    /**
     * @return bool
     */
    public function getReusableToken()
    {
        return $this->getRequest()->getReusableToken();
    }

    /**
     * @param array $addressDetails
     * @param string $country
     * @param bool $isShipping
     * @return bool
     */
    private function isValidCountry($addressDetails, $country, $isShipping = false)
    {
        return $isShipping
            ? ($addressDetails[ValidateRequestInterface::SHIPPING_COUNTRY] == $country)
            : ($addressDetails[ValidateRequestInterface::COUNTRY] == $country);
    }

    /**
     * @param array $addressDetails
     * @param string $country
     * @param bool $isShipping
     * @return bool
     */
    private function isValidCountryAndEmpty($addressDetails, $country, $isShipping = false)
    {
        return $isShipping
            ? ($this->isValidCountry($addressDetails, $country, $isShipping)
                && $addressDetails[ValidateRequestInterface::SHIPPING_POSTAL_CODE] == '')
            : (
                $this->isValidCountry($addressDetails, $country, $isShipping)
                && $addressDetails[ValidateRequestInterface::POSTAL_CODE] == ''
            );
    }
}
