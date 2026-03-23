<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Anonymization\Type;

use Amasty\Gdpr\Model\Anonymization\AbstractType;
use Amasty\Gdpr\Model\CustomerData;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;

class ThirdPartyInformation extends AbstractType
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Mapper
     */
    private $customerMapper;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerDataFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    public function __construct(
        CollectionFactory $customerCollectionFactory,
        CustomerData $customerData,
        CustomerRepositoryInterface $customerRepository,
        Mapper $customerMapper,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        parent::__construct($customerCollectionFactory, $customerData);
        $this->customerRepository = $customerRepository;
        $this->customerMapper = $customerMapper;
        $this->customerDataFactory = $customerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    public function execute(int $customerId)
    {
        $savedCustomerData = $this->customerRepository->getById($customerId);
        $customData = $this->customerMapper->toFlatArray($savedCustomerData);

        $attributeCodes = $this->customerData->getAttributeCodes('customer');
        $exclude = $this->customerData->getAttributeCodes('exclude');
        $exclude = array_merge($exclude, $attributeCodes);
        $inputTypes = $this->customerData->getAttributesInputType(
            array_diff(array_keys($customData), $exclude)
        );

        foreach ($customData as $attributeCode => &$value) {
            if (!in_array($attributeCode, $exclude) && $value) {
                $this->modifyFieldValue($value, $inputTypes[$attributeCode] ?? '');
            }
        }

        $customer = $this->customerDataFactory->create();
        $customData = array_merge(
            $this->customerMapper->toFlatArray($savedCustomerData),
            $customData
        );
        $customData['id'] = $customerId;
        $this->dataObjectHelper->populateWithArray(
            $customer,
            $customData,
            CustomerInterface::class
        );

        $this->setCustomerIgnoreValidationFlag($customer);

        $this->customerRepository->save($customer);
    }

    private function modifyFieldValue(&$value, string $inputType): void
    {
        switch ($inputType) {
            case 'date':
                $value = self::ANONYMOUS_DATE;
                break;
            case 'boolean':
                $value = self::ANONYMIZE_BOOLEAN;
                break;
            default:
                $value = $this->generateFieldValue();
        }
    }
}
