<?php

namespace Ebizmarts\BrippoPayments\Helper;

use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\PaymentMethods\PaymentMethod;
use Ebizmarts\BrippoPayments\Model\Config\Source\CheckoutEmail;
use Ebizmarts\BrippoPayments\Model\Config\Source\CheckoutLocation;
use Ebizmarts\BrippoPayments\Model\Config\Source\CheckoutOnTopValidationMode;
use Ebizmarts\BrippoPayments\Model\Config\Source\CurrencyMode;
use Ebizmarts\BrippoPayments\Model\Config\Source\ExpressLocation;
use Ebizmarts\BrippoPayments\Model\Config\Source\ProductPageBehavior;
use Ebizmarts\BrippoPayments\Model\Express as ExpressPaymentMethod;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Asset\Repository;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProductType;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory as QuoteAddressFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ShippingMethodManagement;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Store\Model\Store;
use Magento\Tax\Helper\Data as TaxHelper;

class Express extends PaymentMethod
{
    const PRODUCT_PAGE_VIEW_NAME = 'catalog_product_view';

    /** @var AddressRepositoryInterface */
    protected $addressRepository;

    /** @var AddressInterfaceFactory */
    protected $addressDataFactory;

    /** @var QuoteAddressFactory */
    protected $quoteAddressFactory;

    /** @var RegionFactory */
    protected $regionFactory;

    /** @var Rate */
    protected $shippingRate;

    /** @var CustomerSession */
    protected $customerSession;

    /** @var QuoteFactory */
    protected $quoteFactory;

    /** @var Product */
    protected $product;

    /** @var GroupedProductType */
    protected $groupedProductType;

    /** @var ShippingMethodManagement */
    protected $shippingMethodManagement;

    /** @var Registry */
    protected $coreRegistry;

    protected $taxHelper;
    protected $priceCurrency;
    protected $assetRepo;
    protected $countryFactory;
    protected $stripeHelper;
    protected $productRepository;
    protected $cart;
    protected $customerRepository;

    /**
     * @param Context $context
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressDataFactory
     * @param RegionFactory $regionFactory
     * @param Rate $shippingRate
     * @param CustomerSession $customerSession
     * @param QuoteFactory $quoteFactory
     * @param Product $product
     * @param TaxHelper $taxHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param Data $dataHelper
     * @param Repository $assetRepo
     * @param CountryFactory $countryFactory
     * @param ShippingMethodManagement $shippingMethodManagement
     * @param Stripe $stripeHelper
     * @param ProductRepositoryInterface $productRepository
     * @param Cart $cart
     * @param Json $json
     * @param EncryptorInterface $encryptor
     * @param CustomerRepositoryInterface $customerRepository
     * @param GroupedProductType $groupedProductType
     * @param QuoteAddressFactory $quoteAddressFactory
     * @param Registry $registry
     */
    public function __construct(
        Context                             $context,
        AddressRepositoryInterface          $addressRepository,
        AddressInterfaceFactory             $addressDataFactory,
        RegionFactory                       $regionFactory,
        Rate                                $shippingRate,
        CustomerSession                     $customerSession,
        QuoteFactory                        $quoteFactory,
        Product                             $product,
        TaxHelper                           $taxHelper,
        PriceCurrencyInterface              $priceCurrency,
        DataHelper                          $dataHelper,
        Repository                          $assetRepo,
        CountryFactory                      $countryFactory,
        ShippingMethodManagement            $shippingMethodManagement,
        Stripe                              $stripeHelper,
        ProductRepositoryInterface          $productRepository,
        Cart                                $cart,
        Json                                $json,
        EncryptorInterface                  $encryptor,
        CustomerRepositoryInterface         $customerRepository,
        GroupedProductType                  $groupedProductType,
        QuoteAddressFactory                 $quoteAddressFactory,
        Registry                            $registry
    ) {
        parent::__construct($context, $dataHelper, $json, $encryptor);
        $this->addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->regionFactory = $regionFactory;
        $this->shippingRate = $shippingRate;
        $this->customerSession = $customerSession;
        $this->quoteFactory = $quoteFactory;
        $this->product = $product;
        $this->taxHelper = $taxHelper;
        $this->priceCurrency = $priceCurrency;
        $this->assetRepo = $assetRepo;
        $this->countryFactory = $countryFactory;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->stripeHelper = $stripeHelper;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->customerRepository = $customerRepository;
        $this->groupedProductType = $groupedProductType;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->coreRegistry = $registry;
    }

    /**
     * @param int $currentProductId
     * @return Quote
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function createQuoteFromScratch(int $currentProductId): Quote
    {
        $quote = $this->quoteFactory->create();
        $quote->setStore($this->dataHelper->getStore());
        $quote->setCurrency();

        /**
         * PRODUCTS
         */
        $product = $this->product->load($currentProductId);
        $quote->addProduct($product, 1);

        if (!$this->customerSession->isLoggedIn()) {
            $quote->setCustomerIsGuest(true);
        } else {
            $customerId = $this->customerSession->getCustomerId();
            $customer = $this->customerRepository->getById($customerId);
            $quote->setCustomer($customer);
        }

        $quote->getBillingAddress()->addData($this->getStoresDefaultShippingAddress());
        $quote->getShippingAddress()->addData($this->getStoresDefaultShippingAddress());

        return $quote->collectTotals();
    }

    /**
     * @param Quote $quote
     * @param $currency
     * @return array
     * @throws NoSuchEntityException
     * @throws InputException
     */
    public function getShippingOptions($quote, $currency): array
    {
        $availableShippingMethods = [];
        $shippingOptions = [];
        $selectedShippingOption = null;

        if ($quote->getShippingAddress() && $quote->getShippingAddress()->getShippingMethod()) {
            $selectedShippingOption = $quote->getShippingAddress()->getShippingMethod();
        }

        if (!empty($quote->getId()) && !$quote->isVirtual() && !empty($quote->getShippingAddress()->getCountryId())) {
            $quote->getShippingAddress()
                ->setCollectShippingRates(true);

            $addressData = $this->prepareShippingDataForShippingEstimate($quote);

            /** @var AddressInterface $addressInterface */
            $addressInterface = $this->quoteAddressFactory->create(['data' => $addressData]);
            $availableShippingMethods = $this->shippingMethodManagement->estimateByExtendedAddress($quote->getId(), $addressInterface);
        }

        $shouldInclTax = $this->shouldCartPriceInclTax($quote->getStore());
        $defaultShippingMethod = null;
        foreach ($availableShippingMethods as $method) {
            if ($method->getErrorMessage()) {
                continue;
            }

            if ($this->isShippingMethodBlocked($method)) {
                continue;
            }

            $amount = $shouldInclTax ? $method->getPriceInclTax() : $method->getPriceExclTax();
            if ($currency === $quote->getBaseCurrencyCode() &&
                $currency !== $quote->getQuoteCurrencyCode() &&
                $method->getBaseAmount() !== $method->getAmount()) {
                /*
                * Amend for different currency
                */
                $rate = $method->getBaseAmount() / $method->getAmount();
                $amount = round($amount * $rate, 2);
            }

            $shippingOptionData = [
                'id' => $method->getCarrierCode() . '_' . $method->getMethodCode(),
                'label' => $method->getCarrierTitle() . ' - ' . $method->getMethodTitle(),
                'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount($amount, $currency)
            ];

            if ($selectedShippingOption == $method->getCarrierCode() . '_' . $method->getMethodCode()) {
                $defaultShippingMethod = $shippingOptionData;
            } else {
                $shippingOptions[] = $shippingOptionData;
            }
        }

        if ($defaultShippingMethod) {
            $shippingOptions = array_merge([$defaultShippingMethod], $shippingOptions);
        }

        return $shippingOptions;
    }

    /**
     * @param Quote $quote
     * @return array
     */
    public function prepareShippingDataForShippingEstimate($quote): array
    {
        return [
            'country_id' => $quote->getShippingAddress() ? $quote->getShippingAddress()->getCountryId() : '',
            'postcode' => $quote->getShippingAddress() ? $quote->getShippingAddress()->getPostcode() : '',
            'region' => $quote->getShippingAddress() ? $quote->getShippingAddress()->getRegion() : '',
            'region_id' => $quote->getShippingAddress() ? $quote->getShippingAddress()->getRegionId() : '',
            'extension_attributes' => [
                'discounts' => []
            ]
        ];
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
        $blockedMethodsConfig = $this->dataHelper->getStoreConfig(
            ExpressPaymentMethod::XML_PATH_BLOCKED_SHIPPING_METHODS
        );
        $blockedMethods = explode(",", $blockedMethodsConfig ?? "");

        if (!empty($blockedMethods)
            && (in_array($methodId, $blockedMethods) || in_array($methodIdAlternative, $blockedMethods))) {
            return true;
        }
        return false;
    }

    /**
     * @param array $shippingOptions
     * @param $shippingOption
     * @return bool
     */
    public function isShippingMethodAvailable($shippingOptions, $shippingOption): bool
    {
        $isValid = false;

        if (!is_array($shippingOptions)) {
            return false;
        }

        foreach ($shippingOptions as $shipping) {
            if (isset($shipping['id']) && $shipping['id'] == $shippingOption) {
                $isValid = true;
            }
        }
        return $isValid;
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

        $street = '';
        if (isset($shippingAddress['addressLine'])) {
            foreach ($shippingAddress['addressLine'] as $streetLine) {
                $street .= empty($street) ? $streetLine : ', ' . $streetLine;
            }
        }

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
            'street' => $street,
            'city' => $shippingAddress['city'] ?? '',
            'country_id' => $shippingAddress['country'],
            'postcode' => $this->cleanPostalCode($shippingAddress['postalCode']),
            'telephone' => $shippingAddress['phone'],
            'region' => $region,
            'region_id' => $regionId
        ];

        $quote->getBillingAddress()->addData($shippingAddressData);
        $quote->getShippingAddress()->addData($shippingAddressData);
        $quote->getShippingAddress()
            ->setCollectShippingRates(true)
            ->collectShippingRates();

        // collect totals to get correct shipping options
        $quote->setTotalsCollectedFlag(false)->collectTotals();

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
            $quote->getShippingAddress()
                ->setShippingMethod($selectedShippingMethod)
                ->setCollectShippingRates(true);
        }

        // collect totals again to get correct grand total
        $quote->setTotalsCollectedFlag(false)->collectTotals();
        $quote->save();
    }

    /**
     * @param Quote $quote
     * @param string $shippingOptionId
     * @return void
     * @throws Exception
     */
    public function setShippingMethodToQuote(Quote $quote, string $shippingOptionId): void
    {
        $quote->getShippingAddress()->setShippingMethod($shippingOptionId);
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals()->save();
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getStoresDefaultShippingAddress(): array
    {
        $address = [];
        $address['country_id'] = $this->dataHelper->getStoreConfig(Shipment::XML_PATH_STORE_COUNTRY_ID);
        $address['postcode'] = $this->dataHelper->getStoreConfig(Shipment::XML_PATH_STORE_ZIP);
        $address['city'] = $this->dataHelper->getStoreConfig(Shipment::XML_PATH_STORE_CITY);
        $address['street'] = $this->dataHelper->getStoreConfig(Shipment::XML_PATH_STORE_ADDRESS1);
        $address['telephone'] = 555;
        if ($regionId = $this->dataHelper->getStoreConfig(Shipment::XML_PATH_STORE_REGION_ID)) {
            $newRegion = $this->regionFactory->create();
            $newRegion->load($regionId);
            $address['region_id'] = $newRegion->getRegionId();
            $address['region'] = $newRegion->getName();
        }

        return $address;
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
        Quote $quote,
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


        $rates = $quote->getShippingAddress()->getShippingRateByCode($quote->getShippingAddress()->getShippingMethod());
        if (empty($rates)) {
            $this->dataHelper->logger->log(
                "Rates empty, carrier: " . $quote->getShippingAddress()->getShippingMethod()
                . " calling collectShippingRates...");
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->getShippingAddress()->collectShippingRates();
        }

        if ($source !== 'checkout' || $this->shouldUseWalletShippingInCheckout($scopeId)) {
            if (!$this->isShippingDataEmpty($shippingData)) {
                $quote->getShippingAddress()->addData($this->getShippingAddressFromPaymentMethod(
                    $shippingData,
                    $payerName,
                    $payerPhone
                ));
            }

            if (!empty($billingData)) {
                $quote->getBillingAddress()->addData($this->getBillingAddressFromPaymentMethod(
                    $billingData,
                    $payerName,
                    $payerPhone
                ));
            }
        }

        if ($source == 'checkout'
            && empty($quote->getShippingAddress()->getShippingMethod()) && !empty($frontendShippingMethod)) {
            $this->dataHelper->logger->log(
                "Quote shipping method is missing at checkout, use frontend data " .
                print_r($frontendShippingMethod, true)
            );

            if (isset($frontendShippingMethod['carrier_code']) && isset($frontendShippingMethod['method_code'])) {
                $shippingId = $frontendShippingMethod['carrier_code'] . "_" . $frontendShippingMethod['method_code'];
                $quote->getShippingAddress()->setShippingMethod($shippingId);
                $this->dataHelper->logger->log("Quote shipping method set to " . $shippingId);
            } else {
                $this->dataHelper->logger->log("Method and carrier code are not set, missing shipping method.");
            }
        }

        if (!$this->isAddressValidForOrderSubmit($quote->getBillingAddress()) && !empty($frontendQuoteBillingAddress)) {
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

        $quote->setPaymentMethod(ExpressPaymentMethod::METHOD_CODE);
        $quote->getPayment()->importData(['method' => ExpressPaymentMethod::METHOD_CODE]);
        $quote->setTotalsCollectedFlag(false);
        return $quote->collectTotals()->save();
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
        if ($this->shouldUseCheckoutEmail($checkoutEmail, $scopeId)) {
            return $checkoutEmail;
        } elseif (!empty($quote->getCustomerEmail())) {
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
     * @param $checkoutEmail
     * @param $scopeId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function shouldUseCheckoutEmail($checkoutEmail, $scopeId): bool
    {
        return $this->dataHelper->getStoreConfig(
                ExpressPaymentMethod::XML_PATH_CHECKOUT_EMAIL,
                $scopeId
            ) === CheckoutEmail::EMAIL_CHECKOUT
            && !empty($checkoutEmail)
            && filter_var($checkoutEmail, FILTER_VALIDATE_EMAIL);
    }

    /**
     * @param $billingData
     * @param string $payerName
     * @param string $payerPhone
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getBillingAddressFromPaymentMethod(
        $billingData,
        string $payerName,
        string $payerPhone
    ): array {
        $nameObject = $this->parseFullName(empty($billingData['name']) ? $payerName : $billingData['name']);
        $firstName = $nameObject->getFirstname();
        $lastName = $nameObject->getLastname();
        $city = (!empty($billingData['address']['city']) ? $billingData['address']['city'] : 'Unspecified');
        $postcode = !empty($billingData['address']['postal_code'])
            ? $billingData['address']['postal_code']
            : 'Unspecified';

        $streetRegionCountryData = $this->getStreetRegionAndCountryFromPaymentData(
            $billingData,
            $billingData['address']['state'] ?? '',
            $billingData['address']['country'] ?? '',
            [
                0 => $billingData['address']['line1'] ?? '',
                1 => $billingData['address']['line2'] ?? ''
            ]
        );

        $billingAddress = [
            'firstname' => $firstName,
            'lastname' => $lastName,
            'company' => '',
            'email' => $billingData['email'],
            'street' => $streetRegionCountryData['street'],
            'city' => $this->sanitizeCity($city),
            'postcode' => $this->cleanPostalCode($postcode),
            'country_id' => $streetRegionCountryData['country_id'],
            'telephone' => empty($billingData['phone']) ? $payerPhone : $billingData['phone']
        ];

        if (isset($streetRegionCountryData['region'])
            && isset($streetRegionCountryData['region_id'])
            && !empty($streetRegionCountryData['region'])
            && !empty($streetRegionCountryData['region_id'])) {
            $billingAddress['region_id'] = $streetRegionCountryData['region_id'];
            $billingAddress['region'] = $streetRegionCountryData['region'];
        } else {
            $billingAddress['region_id'] = null;
            $billingAddress['region'] = '';
        }

        return $billingAddress;
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
        $nameObject = $this->parseFullName(empty($addressData['recipient']) ? $payerName : $addressData['recipient']);
        $firstName = $nameObject->getFirstname();
        $lastName = $nameObject->getLastname();

        $streetRegionCountryData = $this->getStreetRegionAndCountryFromPaymentData(
            $addressData,
            $addressData['region'] ?? '',
            $addressData['country'] ?? '',
            $addressData['addressLine'] ?? ''
        );

        $shippingAddress = [
            'firstname' => $firstName,
            'lastname' => $lastName,
            'company' => $addressData['organization'],
            'email' => '',
            'street' => $streetRegionCountryData['street'],
            'city' => $this->sanitizeCity($addressData['city'] ?? ''),
            'postcode' => $this->cleanPostalCode($addressData['postalCode']),
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
     * Sanitize city name to only allow A-Z, a-z, 0-9, -, ', and spaces
     *
     * @param string $city
     * @return string
     */
    protected function sanitizeCity(string $city): string
    {
        // Remove any characters that are not A-Z, a-z, 0-9, -, ', or spaces
        $sanitized = preg_replace("/[^A-Za-z0-9\-' ]/", '', $city);
        
        // Trim any leading/trailing spaces
        $sanitized = trim($sanitized);
        
        // If the city is empty after sanitization, use a fallback
        if (empty($sanitized)) {
            $sanitized = 'City';
        }
        
        return $sanitized;
    }

    /**
     * @param array $addressData
     * @param string $region
     * @param string $country
     * @param $street
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getStreetRegionAndCountryFromPaymentData(
        array $addressData,
        string $region,
        string $country,
        $street
    ): array {
        $street = empty($street) ? ["Unspecified Street"] : $street;
        $regionId = null;

        if (empty($country)) {
            $note = "UNDEFINED COUNTRY. Using Stores configuration instead.";
            if (count($street) == 3) {
                $street[2] .= ' ' . $note;
            } else {
                $street[count($street)] = $note;
            }

            $storesAddress = $this->getStoresDefaultShippingAddress();
            $country = $storesAddress['country_id'];
            $this->dataHelper->logger->log('Invalid address data provided');
            // phpcs:disable
            $this->dataHelper->logger->log(print_r($addressData, true));
            // phpcs:enable
        } else {
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
        }

        return [
            'street' => $street,
            'region' => $region,
            'country_id' => $country,
            'region_id' => $regionId
        ];
    }

    /**
     * @param $str
     * @return string
     */
    private function cleanString($str): string
    {
        return strtolower(trim($str));
    }

    /**
     * @param $countryCode
     * @return array
     */
    private function getRegionsForCountry($countryCode): array
    {
        $values = [];
        $country = $this->countryFactory->create()->loadByCode($countryCode);

        if (empty($country)) {
            return $values;
        }

        $regions = $country->getRegions();

        foreach ($regions as $region) {
            $values['byCode'][$this->cleanString($region->getCode())] = $region->getId();
            $values['byName'][$this->cleanString($region->getName())] = $region->getId();
        }

        return $values;
    }

    protected function getRegionIdBy($regionName, $regionCountry)
    {
        $regions = $this->getRegionsForCountry($regionCountry);

        $regionName = $this->cleanString($regionName);

        if (isset($regions['byName'][$regionName])) {
            return $regions['byName'][$regionName];
        } elseif (isset($regions['byCode'][$regionName])) {
            return $regions['byCode'][$regionName];
        }

        return null;
    }

    /**
     * @param $fullName
     * @return DataObject
     */
    protected function parseFullName($fullName): DataObject
    {
        $returnData = new DataObject();
        try {
            if (empty($fullName)) {
                throw new LocalizedException(__("No recipient name provided."));
            }
            $nameParts = explode(' ', (string)$fullName);

            $firstName = array_shift($nameParts);
            $returnData->setFirstname(empty($firstName) ? 'Not specified' : $firstName);

            $lastName = implode(" ", $nameParts);
            $returnData->setLastname(empty($lastName) ? 'Not specified' : $lastName);
        } catch (Exception $ex) {
            $this->dataHelper->logger->log($ex->getMessage());
            $returnData->setFirstname('Not specified');
            $returnData->setLastname('Not specified');
        }

        return $returnData;
    }

    /**
     * @param int $storeId
     * @param string $location
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isAvailableAtLocation(int $storeId, string $location):bool
    {
        $enabledLocations = $this->dataHelper->getStoreConfig(
            ExpressPaymentMethod::XML_PATH_STORE_CONFIG_LOCATION,
            $storeId
        );

        if ($enabledLocations == null) {
            return false;
        }

        switch ($location) {
            case 'checkout_cart_index':
                return stripos(
                        str_replace(ExpressLocation::MINICART, "", $enabledLocations),
                        ExpressLocation::CART
                    ) !== false;
            case self::PRODUCT_PAGE_VIEW_NAME:
                return stripos($enabledLocations, ExpressLocation::PRODUCT_PAGE) !== false;
            case 'checkout_index_index':
                return stripos($enabledLocations, ExpressLocation::CHECKOUT) !== false;
            case ExpressLocation::MINICART:
                return stripos($enabledLocations, ExpressLocation::MINICART) !== false;
            case ExpressLocation::PRODUCT_PAGE:
                return stripos($enabledLocations, ExpressLocation::PRODUCT_PAGE) !== false;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getWalletsLogos()
    {
        $params = ['_secure' => $this->_request->isSecure()];

        return [
            'googlePay' => $this->assetRepo->getUrlWithParams(
                'Ebizmarts_BrippoPayments::img/googlepay.png',
                $params
            ),
            'applePay' => $this->assetRepo->getUrlWithParams(
                'Ebizmarts_BrippoPayments::img/applepay.svg',
                $params
            ),
            'link' => $this->assetRepo->getUrlWithParams(
                'Ebizmarts_BrippoPayments::img/link.png',
                $params
            )
        ];
    }

    /**
     * @param Quote $quote
     * @param string $currency
     * @param int $scopeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCartOptions(Quote $quote, string $currency, int $scopeId): array
    {
        $amount = $quote->getGrandTotal();
        $isConfiguredToUseBaseCurrency = $this->dataHelper->getStoreConfig(
                ExpressPaymentMethod::XML_PATH_CURRENCY_MODE,
                $scopeId
            ) === CurrencyMode::MODE_BASE_CURRENCY;
        if ($isConfiguredToUseBaseCurrency) {
            /*
             * Amend for currency mode
             */
            $amount = $quote->getBaseGrandTotal();
        }

        $label = __('Grand Total');
        $businessName = $this->dataHelper->getStoreConfig(
            ExpressPaymentMethod::XML_PATH_STORE_BUSINESS_NAME
        );
        if (!empty($businessName)) {
            $label = $businessName;
        }

        return [
            'total' => [
                'label' => $label,
                'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount($amount, $currency),
                'pending' => false
            ],
            'currency' => strtolower($currency),
            'displayItems' => $this->getDisplayItems($quote, $currency)
        ];
    }

    /**
     * @param $quote
     * @param $currency
     * @return array
     */
    protected function getDisplayItems($quote, $currency): array
    {
        $shouldInclTax = $this->shouldCartPriceInclTax($quote->getStore());

        //Items
        $displayItems = [];
        $taxAmount = 0;
        $items = $quote->getAllVisibleItems();

        if (count($items) > 3) {
            $rowTotal = 0;
            foreach ($items as $item) {
                if ($item->getParentItem()) {
                    continue;
                }

                $rowTotal += $shouldInclTax ? $item->getRowTotalInclTax() : $item->getRowTotal();
                $taxAmount += $item->getTaxAmount();
            }
            $label = __('Items');
            $displayItems[] = [
                'label' => $label,
                'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount($rowTotal, $currency),
                'pending' => false
            ];
        } else {
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
                    'label' => $label,
                    'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount($rowTotal, $currency),
                    'pending' => false
                ];
            }
        }

        //Tax
        if ($taxAmount > 0) {
            $displayItems[] = [
                'label' => __('Tax' . ($shouldInclTax ? '' : ' (INCL)')),
                'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount($taxAmount, $currency)
            ];
        }

        //Shipping
        if (!$quote->getIsVirtual()) {
            $address = $quote->getShippingAddress();
            if ($address->getShippingInclTax() > 0) {
                $price = $shouldInclTax ? $address->getShippingInclTax() : $address->getShippingAmount();
                $displayItems[] = [
                    'label' => (string)__('Shipping'),
                    'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount($price, $currency)
                ];
            }
        }

        //Discount
        $discount = $quote->getSubtotal() - $quote->getSubtotalWithDiscount();
        if ($discount > 0) {
            $displayItems[] = [
                'label' => __('Discount'),
                'amount' => -$this->stripeHelper->convertMagentoAmountToStripeAmount($discount, $currency)
            ];
        }

        return $displayItems;
    }

    /**
     * @param  null|int|string|Store $store
     * @return bool
     */
    protected function shouldCartPriceInclTax($store = null): bool
    {
        if ($this->taxHelper->displayCartBothPrices($store)) {
            return true;
        } elseif ($this->taxHelper->displayCartPriceInclTax($store)) {
            return true;
        }

        return false;
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
            if ($quote->isVirtual()) {
                return false;
            }
            return $location != ExpressLocation::CHECKOUT ||
                ($this->shouldUseWalletShippingInCheckout($scopeId));
        } catch (Exception $ex) {
            $this->dataHelper->logger->log($ex->getMessage());
            return true;
        }
    }

    /**
     * @param $scopeId
     * @return bool
     */
    protected function shouldUseWalletShippingInCheckout($scopeId): bool
    {
        try {
            return $this->dataHelper->getStoreConfig(
                    ExpressPaymentMethod::XML_PATH_CHECKOUT_LOCATION,
                    $scopeId
                ) === CheckoutLocation::LOCATION_ON_TOP
                && $this->dataHelper->getStoreConfig(
                    ExpressPaymentMethod::XML_PATH_CHECKOUT_VALIDATION_MODE,
                    $scopeId
                ) === CheckoutOnTopValidationMode::ONLY_AGREEMENTS;
        } catch (Exception $ex) {
            $this->dataHelper->logger->log($ex->getMessage());
            return false;
        }
    }

    /**
     * @param $original
     * @return string
     */
    protected function cleanPostalCode($original)
    {
        return trim($original);
    }

    /**
     * @param $shippingData
     * @return bool
     */
    protected function isShippingDataEmpty($shippingData)
    {
        return empty($shippingData)
            || empty($shippingData['postalCode'])
            || empty($shippingData['city']);
    }

    /**
     * @param int|null $scopeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getExpressButtonHeight(?int $scopeId = null): string
    {
        $height = $this->dataHelper->getStoreConfig(
            ExpressPaymentMethod::XML_PATH_BUTTON_HEIGHT,
            $scopeId
        );

        if (empty($height)) {
            $height = '50';
        }

        return  $height . 'px';
    }

    /**
     * @param $source
     * @return string|null
     */
    public function getFrontendSourceBeautified($source): ?string
    {
        if ($source == null) {
            return null;
        }

        switch ($source) {
            case ExpressLocation::CHECKOUT:
            case ExpressLocation::CHECKOUT_LIST:
                return __('Checkout');
            case ExpressLocation::MINICART:
                return __('Minicart');
            case ExpressLocation::CART:
                return __('Cart');
            case ExpressLocation::PRODUCT_PAGE:
                return __('Product Page');
            default:
                return $source;
        }
    }

    /**
     * @param array $shippingAddressData
     * @return bool
     */
    public function isGBPostalCodeAndNeedsFixing(array $shippingAddressData): bool
    {
        if ($shippingAddressData['country'] !== 'GB') {
            return false;
        }
        $postalCode = (string)($shippingAddressData['postalCode']
            ?? $shippingAddressData['postal_code']
            ?? '');

        return count(explode(' ', $postalCode)) === 1;
    }

    /**
     * @param array $shippingAddressData
     * @return array
     */
    public function fixGBPostalCode(array $shippingAddressData): array
    {
        if (array_key_exists('postalCode', $shippingAddressData)) {
            $postalCodeParts = explode(' ', (string)$shippingAddressData['postalCode']);
            $shippingAddressData['postalCode'] = $postalCodeParts[0] . ' 1AA';
        } else if (array_key_exists('postal_code', $shippingAddressData)) {
            $postalCodeParts = explode(' ', (string)$shippingAddressData['postal_code']);
            $shippingAddressData['postal_code'] = $postalCodeParts[0] . ' 1AA';
        }

        return $shippingAddressData;
    }

    /**
     * @param $message
     * @param $billingAddressData
     * @param $shippingAddressData
     * @return string
     */
    public function prettifyErrorMessage($message, $billingAddressData, $shippingAddressData)
    {
        try {
            if (strpos($message, 'check the billing address information') !== false
                && !empty($billingAddressData)
                && !empty($billingAddressData['address'])
            ) {
                if (strpos($message, 'Invalid value') !== false
                    && strpos($message, 'countryId field') !== false
                    && !empty($billingAddressData['address']['country'])
                ) {
                    $this->dataHelper->logger->log($message);
                    return __('Please check the billing address information. Invalid country code:')->getText()
                        . ' ' . $billingAddressData['address']['country'];
                } elseif (strpos($message, 'Invalid value') !== false
                    && strpos($message, 'regionId field') !== false
                    && !empty($billingAddressData['address']['state'])
                ) {
                    $this->dataHelper->logger->log($message);
                    return __('Please check the billing address information. Invalid region:')->getText()
                        . ' ' . $billingAddressData['address']['state'];
                }
            } elseif (strpos($message, 'check the shipping address information') !== false
                && !empty($shippingAddressData)
            ) {
                if (strpos($message, 'Invalid value') !== false
                    && strpos($message, 'countryId field') !== false
                    && !empty($shippingAddressData['country'])
                ) {
                    $this->dataHelper->logger->log($message);
                    return __('Please check the shipping address information. Invalid country code:')->getText()
                        . ' ' . $shippingAddressData['country'];
                } elseif (((strpos($message, 'Invalid value') !== false && strpos($message, 'regionId field') !== false) ||
                        (strpos($message, 'regionId') !== false && strpos($message, 'is required') !== false))
                    && !empty($shippingAddressData['region'])
                ) {
                    $this->dataHelper->logger->log($message);
                    return __('Please check the shipping address information. Invalid region:')->getText()
                        . ' ' . $shippingAddressData['region'];
                }
            } elseif (strpos($message, 'No valid payment method types for this Payment Intent') !== false
            ) {
                $this->dataHelper->logger->log($message);
                return __('The amount specified is below the minimum required.')->getText();
            }
        } catch (Exception $ex) {
            $this->dataHelper->logger->log($ex->getMessage());
        }

        return $message;
    }

    /**
     * @param Quote $quote
     * @param $requestParams
     * @param $storeId
     * @return Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function setUpProductPageQuote(Quote $quote, $requestParams, $storeId)
    {
        $childProductIds = [];
        $productId = $requestParams['product'];
        $product = $this->productRepository->getById($productId, false, $storeId);


        if ($product->getTypeId() === GroupedProductType::TYPE_CODE) {
            $childProductIds = $this->groupedProductType->getAssociatedProductIds($product);
        }

        $behavior = $this->dataHelper->getStoreConfig(ExpressPaymentMethod::XML_PATH_STORE_PRODUCT_PAGE_BEHAVIOR);

        if ($behavior === ProductPageBehavior::BEHAVIOR_CLEAN_CART) {
            foreach ($quote->getAllVisibleItems() as $quoteItem) {
                $this->cart->removeItem($quoteItem->getId());
            }
        } else {
            if ($behavior === ProductPageBehavior::BEHAVIOR_MAINTAIN_CART) {
                foreach ($quote->getAllVisibleItems() as $quoteItem) {
                    if ($quoteItem->getProductType() == GroupedProductType::TYPE_CODE) {
                        if (in_array($quoteItem->getProduct()->getId(), $childProductIds)) {
                            $this->cart->removeItem($quoteItem->getId());
                        }
                    } elseif ($quote->hasProductId($productId)) {
                        if ($quoteItem->getProduct()->getId() === $productId) {
                            $this->cart->removeItem($quoteItem->getId());
                        }
                    }
                }
            }
        }

        $item = $this->cart->addProduct($product, $requestParams);
        if ($item->getHasError()) {
            throw new LocalizedException(__($item->getMessage()));
        }

        return $quote;
    }

    /**
     * @param Quote $quote
     * @param int $scopeId
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getModalCurrencyFromQuote(Quote $quote, int $scopeId): string
    {
        return $this->getModalCurrency(
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
    public function getModalCurrencyFromOrder(OrderInterface $order, int $scopeId): string
    {
        return $this->getModalCurrency(
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
    protected function getModalCurrency($currencyCode, $baseCurrency, Store $store, int $scopeId): string
    {
        $isConfiguredToUseBaseCurrency = $this->dataHelper->getStoreConfig(
                ExpressPaymentMethod::XML_PATH_CURRENCY_MODE,
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
}
