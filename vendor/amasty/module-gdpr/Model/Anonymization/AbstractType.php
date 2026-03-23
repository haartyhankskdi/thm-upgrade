<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Anonymization;

use Amasty\Gdpr\Model\CustomerData;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Sales\Api\Data\OrderAddressInterface as OrderAddressInterface;
use Magento\Sales\Model\Order\Address as OrderAddress;

abstract class AbstractType
{
    /**#@+
     * Constants defined for AnonymizerPool keys
     */
    public const TYPE_CUSTOMER = 'customer';
    public const TYPE_ORDER = 'order';
    public const TYPE_QUOTES = 'quotes';
    public const TYPE_PERSISTENT_SESSION = 'persistent_session';
    public const TYPE_GIFT_REGISTRY = 'gift_registry';
    public const TYPE_THIRD_PARTY = 'third_party';
    /**#@-*/

    public const ANONYMOUS_PREFIX = 'anonymous';
    public const ANONYMOUS_SYMBOL = '-';
    public const RANDOM_LENGTH = 5;
    public const ANONYMOUS_DATE = '1900-01-01';
    public const ANONYMIZE_IP = '0.0.0.0';
    public const ANONYMIZE_REGION_ID = 0;
    public const ANONYMIZE_COUNTRY_ID = "0";
    public const ANONYMIZE_BOOLEAN = 0;

    /**
     * @var bool
     */
    protected $isDeleting = false;

    /**
     * @var CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var CustomerData
     */
    protected $customerData;

    public function __construct(
        CollectionFactory $customerCollectionFactory,
        CustomerData $customerData
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->customerData = $customerData;
    }

    abstract public function execute(int $customerId);

    public function setIsDeleting(bool $value): void
    {
        $this->isDeleting = $value;
    }

    public function generateFieldValue(): string
    {
        $rand = self::ANONYMOUS_SYMBOL;

        if (!$this->isDeleting) {
            $rand = self::ANONYMOUS_PREFIX . $this->getRandomString();
        }

        return $rand;
    }

    private function getRandomString(): string
    {
        return bin2hex(openssl_random_pseudo_bytes(self::RANDOM_LENGTH));
    }

    public function getRandomEmail(): string
    {
        $email = self::ANONYMOUS_SYMBOL;

        if (!$this->isDeleting) {
            $email = $this->generateFieldValue();
        }

        $email = $email . '@' . $this->getRandomString() . '.com';

        if ($this->isEmailExists($email)) {
            $email = $this->getRandomEmail();
        }

        return $email;
    }

    public function isEmailExists($email): bool
    {
        $collection = $this->customerCollectionFactory->create();

        return (bool)$collection->addFieldToFilter('email', $email)->getSize();
    }

    /**
     * @param \Magento\Quote\Model\Quote|\Magento\Sales\Model\Order $item
     *
     * @return bool
     */
    protected function isAlreadyAnonymized($item): bool
    {
        if ($this->isDeleting) {
            return $item->getCustomerFirstname() == self::ANONYMOUS_SYMBOL
                && $item->getCustomerLastname() == self::ANONYMOUS_SYMBOL
                && strpos((string)$item->getCustomerEmail(), self::ANONYMOUS_SYMBOL . '@') === 0
                && $item->getRemoteIp() == self::ANONYMIZE_IP;
        } else {
            return strpos((string)$item->getCustomerFirstname(), self::ANONYMOUS_PREFIX) === 0
                && strpos((string)$item->getCustomerLastname(), self::ANONYMOUS_PREFIX) === 0
                && strpos((string)$item->getCustomerEmail(), self::ANONYMOUS_PREFIX) === 0
                && $item->getRemoteIp() == self::ANONYMIZE_IP;
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote|\Magento\Sales\Model\Order $object
     */
    protected function prepareSalesData($object): void
    {
        $object->setCustomerFirstname($this->generateFieldValue());
        $object->setCustomerMiddlename($this->generateFieldValue());
        $object->setCustomerLastname($this->generateFieldValue());
        $object->setCustomerEmail($this->getRandomEmail());
        $object->setRemoteIp(self::ANONYMIZE_IP);

        if ($object->getBillingAddress()) {
            $this->anonymizeAddress($object->getBillingAddress());
        }

        if ($object->getShippingAddress()) {
            $this->anonymizeAddress($object->getShippingAddress());
        }
    }

    /**
     * @param DataObject|OrderAddress|QuoteAddress|OrderAddressInterface|CustomerAddressInterface|null $address
     */
    protected function anonymizeAddress($address): void
    {
        $addressArrayData = method_exists($address, '__toArray')
            ? $address->__toArray()
            : $address->toArray();
        $attributeCodes = $this->customerData->getAttributeCodes('customer_address');

        foreach ($attributeCodes as $attributeCode) {
            switch ($attributeCode) {
                case 'telephone':
                case 'fax':
                    $randomString = '0000000';
                    break;
                case 'country_id':
                    $randomString = self::ANONYMIZE_COUNTRY_ID;
                    break;
                case 'region_id':
                    $randomString = self::ANONYMIZE_REGION_ID;
                    break;
                case 'region':
                    $region = $address->getRegion();

                    if (is_object($region)) {
                        $region->setRegion($this->generateFieldValue());
                        $region->setRegionCode($this->generateFieldValue());
                        $region->setRegionId(self::ANONYMIZE_REGION_ID);
                    } else {
                        $region = $this->generateFieldValue();
                    }
                    $randomString = $region;
                    break;
                default:
                    $randomString = $this->generateFieldValue();
            }

            if (!empty($addressArrayData[$attributeCode])) {
                $address->setData($attributeCode, $randomString);
            }
        }
    }

    /**
     * Set ignore_validation_flag to skip unnecessary address and customer validation
     */
    protected function setCustomerIgnoreValidationFlag(CustomerInterface $customer): void
    {
        $customer->setData('ignore_validation_flag', true);
    }
}
