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
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Persistent\Model\Session;

class PersistentSession extends AbstractType
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var PersistentSession
     */
    private $persistentSession;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        CollectionFactory $customerCollectionFactory,
        CustomerData $customerData,
        CustomerRepositoryInterface $customerRepository,
        ObjectManagerInterface $objectManager,
        Manager $moduleManager
    ) {
        parent::__construct($customerCollectionFactory, $customerData);
        $this->objectManager = $objectManager;
        $this->customerRepository = $customerRepository;

        if ($moduleManager->isEnabled('Magento_Persistent')) {
            $this->initializePersistentSession();
        }
    }

    private function initializePersistentSession(): void
    {
        try {
            $this->persistentSession = $this->objectManager->get(Session::class);
        } catch (\Throwable $t) {
            null;
        }
    }

    public function execute(int $customerId)
    {
        if ($this->persistentSession) {
            $session = $this->persistentSession->loadByCustomerId($customerId);

            if ($session->getId()) {
                $customerData = $this->customerRepository->getById($session->getCustomerId())->__toArray();
                $attributeCodes = $this->customerData->getAttributeCodes('customer');

                foreach ($attributeCodes as $attributeCode) {
                    if ($session->getData($attributeCode)) {
                        $session->setData($attributeCode, $customerData[$attributeCode]);
                    }
                }

                $session->setInfo(null);
                $session->save();
            }
        }
    }
}
