<?php

namespace Ebizmarts\BrippoPayments\Helper;

use Ebizmarts\BrippoPayments\Model\Config\Source\ExpressLocation;
use Exception;
use Magento\Directory\Model\Region;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote;
use Ebizmarts\BrippoPayments\Model\ExpressCheckoutElement as ExpressCheckoutElementMethod;
use Magento\Sales\Api\Data\OrderInterface;

class ExpressCheckoutElement extends Express
{
    /**
     * @param $quote
     * @param $currency
     * @return array
     * @throws NoSuchEntityException
     * @throws InputException
     */
    public function getShippingOptions($quote, $currency): array
    {
        $availableShippingMethods = [];
        $shippingOptions = [];
        $selectedShippingMethod = null;

        if ($quote->getShippingAddress() && $quote->getShippingAddress()->getShippingMethod()) {
            $selectedShippingMethod = $quote->getShippingAddress()->getShippingMethod();
        }

        if (!empty($quote->getId()) && !$quote->isVirtual() && !empty($quote->getShippingAddress()->getCountryId())) {
            $addressData = $this->prepareShippingDataForShippingEstimate($quote);

            /** @var AddressInterface $addressInterface */
            $addressInterface = $this->quoteAddressFactory->create(['data' => $addressData]);
            $availableShippingMethods = $this->shippingMethodManagement->estimateByExtendedAddress($quote->getId(), $addressInterface);
        }

        $shouldInclTax = $this->shouldCartPriceInclTax($quote->getStore());

        foreach ($availableShippingMethods as $method) {
            if ($method->getErrorMessage()) {
                continue;
            }

            if ($this->isShippingMethodBlocked($method)) {
                continue;
            }

            $optionId = $method->getCarrierCode() . '_' . $method->getMethodCode();
            $option = [
                'id' => $optionId,
                'displayName' => $method->getCarrierTitle() . ' - ' . $method->getMethodTitle(),
                'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount(
                    $shouldInclTax ? $method->getPriceInclTax() : $method->getPriceExclTax(),
                    $currency
                ),
                //'deliveryEstimate' => ???? toDo
                //                  maximum: {unit: 'day', value: 7},
                //             minimum: {unit: 'day', value: 5}
            ];

            if (!empty($selectedShippingMethod) && $selectedShippingMethod === $optionId) {
                array_unshift($shippingOptions, $option);
            } else {
                $shippingOptions[] = $option;
            }
        }

        return $shippingOptions;
    }

    /**
     * @param $method
     * @return bool
     * @throws NoSuchEntityException
     */
    protected function isShippingMethodBlocked($method): bool
    {
        $methodId = $method->getCarrierCode() . '_' . $method->getMethodCode();
        $methodIdAlternative = $method->getCarrierCode() . '_' . $method->getCarrierCode();
        $blockedMethods = $this->dataHelper->getStoreConfig(
            ExpressCheckoutElementMethod::XML_PATH_BLOCKED_SHIPPING_METHODS
        );

        if (!empty($blockedMethods)
            && (strpos($blockedMethods, $methodId) !== false || strpos($blockedMethods, $methodIdAlternative) !== false)) {
            return true;
        }
        return false;
    }

    /**
     * @param Quote $quote
     * @param array $shippingAddress
     * @param string $currency
     * @return void
     * @throws Exception
     */
    public function setShippingAddressToQuoteFromPaymentRequestData(
        Quote $quote,
        array $shippingAddress,
        string $currency
    ) {
        $selectedShippingMethod = $quote->getShippingAddress()->getShippingMethod();

//        $street = '';
//        if (isset($shippingAddress['addressLine'])) {
//            foreach ($shippingAddress['addressLine'] as $streetLine) {
//                $street .= empty($street) ? $streetLine : ', ' . $streetLine;
//            }
//        }

        $region = '';
        $regionId = 0;
        if (isset($shippingAddress['region'])) {
            /** @var Region $region */
            $newRegion = $this->regionFactory->create();
            $newRegion->loadByCode($shippingAddress['region'], $shippingAddress['country']);
            $regionId = $newRegion->getRegionId() ? $newRegion->getRegionId() : 0;
            $region = $newRegion->getName();
        }

        $shippingAddressData = [
            'firstname' => $quote->getCustomerFirstname(),
            'lastname' => $quote->getCustomerLastname(),
            'street' => '',
            'city' => $shippingAddress['city'],
            'country_id' => $shippingAddress['country'],
            'postcode' => $this->cleanPostalCode($this->getPostalCodeFromAddress($shippingAddress)),
            'telephone' => '',
            'region' => $region,
            'region_id' => $regionId
        ];

        $quote->getBillingAddress()->addData($shippingAddressData);
        $quote->getShippingAddress()->addData($shippingAddressData);
        $quote->getShippingAddress()
            ->setCollectShippingRates(true)
            ->collectShippingRates();

        /*
         * SET SELECTED SHIPPING METHOD
         */
        $shippingOptions = $this->getShippingOptions($quote, $currency);

        $defaultOption = '';
        $selectedShippingMethodIsAvailable = false;
        foreach ($shippingOptions as $option) {
            if (empty($defaultOption)) {
                $defaultOption = $option['id'];
            }
            if ($option['id'] == $selectedShippingMethod) {
                $selectedShippingMethodIsAvailable = true;
            }
        }
        if (!$selectedShippingMethodIsAvailable) {
            $selectedShippingMethod = $defaultOption;
        }
        if (!empty($selectedShippingMethod)) {
            $quote->getShippingAddress()->setShippingMethod($selectedShippingMethod);
        }

        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals()->save();
    }

    /**
     * @param Quote $quote
     * @param $shippingData
     * @param $billingData
     * @param string $customerEmail
     * @param string $payerName
     * @param string $payerPhone
     * @param string $source
     * @param $frontendQuoteBillingAddress
     * @param $frontendQuoteShippingAddress
     * @param $frontendShippingMethod
     * @param $scopeId
     * @return Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function fillMissingDataForPlaceOrder(
        Quote  $quote,
               $shippingData,
               $billingData,
        string $customerEmail,
        string $payerName,
        string $payerPhone,
        string $source,
               $frontendQuoteBillingAddress,
               $frontendQuoteShippingAddress,
               $frontendShippingMethod,
               $scopeId
    ): Quote {
        $nameObject = $this->parseFullName($payerName);
        $firstName = $nameObject->getFirstname();
        $lastName = $nameObject->getLastname();

        if (!$this->isShippingDataEmpty($shippingData)) {
            $quote->getShippingAddress()->addData($this->getShippingAddressFromPaymentMethod(
                $shippingData,
                $payerName,
                $payerPhone
            ));

            if (!empty($billingData)) {
                $quote->getBillingAddress()->addData($this->getBillingAddressFromPaymentMethod(
                    $billingData,
                    $payerName,
                    $payerPhone
                ));
            }
        }

        if ($source === ExpressLocation::CHECKOUT_LIST
            || $source === ExpressLocation::CHECKOUT
            && empty($quote->getShippingAddress()->getShippingMethod()) && !empty($frontendShippingMethod)) {
            if (isset($frontendShippingMethod['carrier_code']) && isset($frontendShippingMethod['method_code'])) {
                $shippingId = $frontendShippingMethod['carrier_code'] . "_" . $frontendShippingMethod['method_code'];
                $quote->getShippingAddress()->setShippingMethod($shippingId);
                $this->dataHelper->logger->log("Quote shipping method set to " . $shippingId);
            }
        }

        if (!empty($frontendQuoteBillingAddress)
            && ($source === ExpressLocation::CHECKOUT_LIST
                || !$this->isAddressValidForOrderSubmit($quote->getBillingAddress()))) {
            $this->setBillingAddressFromFrontendData($quote, $frontendQuoteBillingAddress);
        }

        if (!$quote->isVirtual()
            && !$this->isAddressValidForOrderSubmit($quote->getShippingAddress())
            && !empty($frontendQuoteShippingAddress)) {
            $this->setShippingAddressFromFrontendData($quote, $frontendQuoteShippingAddress);
        }

        if (!$this->customerSession->isLoggedIn()) {
            $quote->setCustomerIsGuest(true);
            if (filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
                $quote->setCustomerEmail($customerEmail);
            } else {
                $quote->setCustomerEmail('guest@example.com');
            }
            $quote->setCustomerFirstname($firstName);
            $quote->setCustomerLastname($lastName);
        }

        $quote->setPaymentMethod(ExpressCheckoutElementMethod::METHOD_CODE);
        $quote->getPayment()->importData(['method' => ExpressCheckoutElementMethod::METHOD_CODE]);
        $quote->setTotalsCollectedFlag(false);
        return $quote->collectTotals()->save();
    }

    /**
     * @param $addressData
     * @param string $payerName
     * @param string $payerPhone
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getShippingAddressFromPaymentMethod(
        $addressData,
        string $payerName,
        string $payerPhone
    ): array {
        $nameObject = $this->parseFullName(empty($addressData['name']) ? $payerName : $addressData['name']);
        $firstName = $nameObject->getFirstname();
        $lastName = $nameObject->getLastname();

        $streetRegionCountryData = $this->getStreetRegionAndCountryFromPaymentData(
            $addressData,
            $addressData['address']['state'] ?? '',
            $addressData['address']['country'] ?? '',
            $addressData['address']['line1'] ?? '',
            $addressData['address']['line2'] ?? ''
        );

        $shippingAddress = [
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => '',
            'street' => $streetRegionCountryData['street'],
            'city' => $this->sanitizeCity($addressData['address']['city'] ?? ''),
            'postcode' => $this->cleanPostalCode($this->getPostalCodeFromAddress($addressData['address'])),
            'country_id' => $streetRegionCountryData['country_id'],
            'telephone' => empty($addressData['phone']) ? $payerPhone : $addressData['phone']
        ];

        if (isset($streetRegionCountryData['region'])
            && isset($streetRegionCountryData['region_id'])
            && !empty($streetRegionCountryData['region'])
            && !empty($streetRegionCountryData['region_id'])) {
            $shippingAddress['region_id'] = $streetRegionCountryData['region_id'];
            $shippingAddress['region'] = $streetRegionCountryData['region'];
        }

        return $shippingAddress;
    }

    /**
     * @param array $addressData
     * @param string $region
     * @param string $country
     * @param $streetLine1
     * @param $streetLine2
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getStreetRegionAndCountryFromPaymentData(
        array $addressData,
        string $region,
        string $country,
        $streetLine1,
        $streetLine2 = null
    ): array {
        $street = empty($streetLine1) ? "Unspecified street" : $streetLine1;
        if (!empty($streetLine2)) {
            $street .= ', ' . $streetLine2;
        }
        $regionId = null;

        try {
            if (!empty($region)) {
                $regionId = $this->getRegionIdBy($region, $country);
            }
        } catch (Exception $ex) {
            $storesAddress = $this->getStoresDefaultShippingAddress();
            $region = $storesAddress['region'];
            $country = $storesAddress['country_id'];
            $regionId = $storesAddress['region_id'];
            $this->dataHelper->logger->log($ex->getMessage());
            // phpcs:disable
            $this->dataHelper->logger->log(print_r($addressData, true));
            // phpcs:enable
        }

        return [
            'street' => $street,
            'region' => $region,
            'country_id' => $country,
            'region_id' => $regionId
        ];
    }

    /**
     * @param $quote
     * @param $currency
     * @return array
     */
    public function getLineItems($quote, $currency): array
    {
        return $this->getLineItemsData(
            $currency,
            $this->shouldCartPriceInclTax($quote->getStore()),
            $quote->getAllVisibleItems(),
            $quote->getIsVirtual(),
            $quote->getShippingAddress(),
            $quote->getSubtotal() - $quote->getSubtotalWithDiscount(),
            $quote->getGrandTotal()
        );
    }

    /**
     * @param OrderInterface $order
     * @param $currency
     * @return array|array[]
     */
    public function getLineItemsFromOrder(OrderInterface $order, $currency): array
    {
        return $this->getLineItemsData(
            $currency,
            $this->shouldCartPriceInclTax($order->getStore()),
            $order->getAllVisibleItems(),
            $order->getIsVirtual(),
            $order->getShippingAddress(),
            $order->getSubtotal() - $order->getDiscountAmount(),
            $order->getGrandTotal()
        );
    }

    /**
     * @param $currency
     * @param $shouldInclTax
     * @param $items
     * @param $isVirtual
     * @param $address
     * @param $discount
     * @param $grandTotal
     * @return array|array[]
     */
    protected function getLineItemsData(
        $currency,
        $shouldInclTax,
        $items,
        $isVirtual,
        $address,
        $discount,
        $grandTotal
    ): array {
        $lineItemsTotal = 0;

        //Items
        $displayItems = [];
        $taxAmount = 0;

        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            $rowTotal = $shouldInclTax ? $item->getRowTotalInclTax() : $item->getRowTotal();
            $price = $shouldInclTax ? $item->getPriceInclTax() : $item->getPrice();
            $taxAmount += $item->getTaxAmount();

            $label = $item->getName();
            if ($item->getQty() > 1) {
                $formattedPrice = $this->priceCurrency->format($price, false);
                $label .= sprintf(' (%s x %s)', $item->getQty(), $formattedPrice);
            }

            $displayItems[] = [
                'name' => $label,
                'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount($rowTotal, $currency)
            ];
            $lineItemsTotal += $rowTotal;
        }

        //Tax
        if ($taxAmount > 0) {
            $displayItems[] = [
                'name' => __('Tax' . ($shouldInclTax ? '' : ' (INCL)')),
                'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount($taxAmount, $currency)
            ];
            $lineItemsTotal += $taxAmount;
        }

        //Shipping
        if (!$isVirtual) {
            if ($address->getShippingInclTax() > 0) {
                $price = $shouldInclTax ? $address->getShippingInclTax() : $address->getShippingAmount();
                //$price = $address->getShippingInclTax();
                $displayItems[] = [
                    'name' => (string)__('Shipping'),
                    'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount($price, $currency)
                ];
                $lineItemsTotal += $price;
            }
        }

        //Discount
        if ($discount > 0) {
            $displayItems[] = [
                'name' => __('Discount'),
                'amount' => -$this->stripeHelper->convertMagentoAmountToStripeAmount($discount, $currency)
            ];
        }

        if ((float)$lineItemsTotal !== (float)$grandTotal) {
            $displayItems = [[
                'name' => (string)__('Grand Total'),
                'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount($grandTotal, $currency)
            ]];
        }

        return $displayItems;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getPaymentMethodsOrder() : array
    {
        $order = [];
        $sortOrderApplePay = $this->dataHelper->getStoreConfig(
            ExpressCheckoutElementMethod::XML_PATH_WALLETS_APPLE_PAY_ORDER
        );
        $sortOrderGooglePay = $this->dataHelper->getStoreConfig(
            ExpressCheckoutElementMethod::XML_PATH_WALLETS_GOOGLE_PAY_ORDER
        );
        $sortOrderLink = $this->dataHelper->getStoreConfig(
            ExpressCheckoutElementMethod::XML_PATH_WALLETS_LINK_ORDER
        );

        $order[] = 'applePay';
        if ($sortOrderGooglePay > $sortOrderApplePay) {
            $order[] = 'googlePay';
        } else {
            array_unshift($order, 'googlePay');
        }

        if ($sortOrderLink > $sortOrderApplePay && $sortOrderLink > $sortOrderGooglePay) {
            $order[] = 'link';
        } elseif (($order[1] === 'googlePay' && $sortOrderLink > $sortOrderGooglePay) ||
            ($order[1] === 'applePay' && $sortOrderLink > $sortOrderApplePay)) {
            array_splice($order, 1, 0, 'link');
        } else {
            array_unshift($order, 'link');
        }
        return $order;
    }

    /**
     * @param int $storeId
     * @param string $location
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isAvailableAtLocation(int $storeId, string $location):bool
    {
        switch ($location) {
            case 'checkout_cart_index':
                return $this->dataHelper->getStoreConfig(
                        ExpressCheckoutElementMethod::XML_PATH_LOCATIONS_CART,
                        $storeId
                    ) == true;
            case self::PRODUCT_PAGE_VIEW_NAME:
                return $this->dataHelper->getStoreConfig(
                        ExpressCheckoutElementMethod::XML_PATH_LOCATIONS_PRODUCT,
                        $storeId
                    ) == true;
            case 'checkout_index_index':
                return $this->dataHelper->getStoreConfig(
                        ExpressCheckoutElementMethod::XML_PATH_LOCATIONS_CHECKOUT,
                        $storeId
                    ) == true;
            case ExpressLocation::MINICART:
                return $this->dataHelper->getStoreConfig(
                        ExpressCheckoutElementMethod::XML_PATH_LOCATIONS_MINICART,
                        $storeId
                    ) == true;
        }

        return false;
    }

    /**
     * @param $shippingData
     * @return bool
     */
    protected function isShippingDataEmpty($shippingData): bool
    {
        return empty($shippingData)
            || empty($shippingData['address'])
            || empty($this->getPostalCodeFromAddress($shippingData['address']))
            || empty($shippingData['address']['city']);
    }

    /**
     * @param $address
     * @return string
     */
    protected function getPostalCodeFromAddress($address): string
    {
        if (!empty($address)) {
            if (array_key_exists('postalCode', $address)) {
                return $address['postalCode'];
            } else if (array_key_exists('postal_code', $address)) {
                return $address['postal_code'];
            }
        }
        return "";
    }

    public function setParamsFromRequestBody($request): void
    {
        if (!empty($request->getContent())) {
            try {
                $contentType = $request->getHeader('Content-Type');

                // Handle URL-encoded form data
                if ($contentType && strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
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
                $this->dataHelper->logger->log("Failed to unserialize request body: ". $e->getMessage());
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
     * @param $walletEmail
     * @param $checkoutEmail
     * @param $quote
     * @param $scopeId
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getCustomerEmail($walletEmail, $checkoutEmail, $quote, $scopeId): ?string
    {
        if (!empty($quote->getCustomerEmail())) {
            return $quote->getCustomerEmail();
        } elseif (filter_var($walletEmail, FILTER_VALIDATE_EMAIL)) {
            return $walletEmail;
        } elseif (!empty($quote->getBillingAddress()) &&
            filter_var($quote->getBillingAddress()->getEmail(), FILTER_VALIDATE_EMAIL)) {
            return $quote->getBillingAddress()->getEmail();
        }
        return null;
    }

    /**
     * @param Quote $quote
     * @param string $location
     * @param $scopeId
     * @return bool
     */
    public function shouldRequestShipping(Quote $quote, string $location, $scopeId): bool
    {
        try {
            if ($quote->isVirtual()
                || $location === ExpressLocation::CHECKOUT_LIST) {
                return false;
            }
        } catch (Exception $ex) {
            $this->dataHelper->logger->log($ex->getMessage());
        }
        return true;
    }
}
