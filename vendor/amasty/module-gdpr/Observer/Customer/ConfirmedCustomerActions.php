<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Observer\Customer;

use Amasty\Gdpr\Model\Consent\RegistryConstants;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class ConfirmedCustomerActions implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        ManagerInterface $eventManager
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->eventManager = $eventManager;
    }

    /**
     * @param Observer $observer
     *
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $submittedFrom = $this->request->getParam(RegistryConstants::CONSENT_FROM);
        $consentsCodes = (array)$this->request->getParam(RegistryConstants::CONSENTS, []);
        $customerId = null;
        $email = $this->request->getParam('email');

        if ($customer = $observer->getData('customer')) {
            $customerId = (int)$customer->getId();
        }

        $this->processConsentCodes($consentsCodes, $submittedFrom, $customerId, null, $email);
    }

    /**
     * @param array $codes
     * @param string|null $from
     * @param int|null $customerId
     * @param int|null $storeId
     * @param string|null $email
     *
     * @throws NoSuchEntityException
     */
    protected function processConsentCodes(
        array $codes,
        ?string $from,
        ?int $customerId = null,
        ?int $storeId = null,
        ?string $email = null
    ): void {
        $storeId = $storeId === null ? (int)$this->storeManager->getStore()->getId() : $storeId;

        if (!empty($codes) && $from) {
            $this->eventManager->dispatch(
                'amasty_gdpr_consent_accept',
                [
                    RegistryConstants::CONSENTS => $codes,
                    RegistryConstants::CONSENT_FROM => $from,
                    RegistryConstants::CUSTOMER_ID => $customerId,
                    RegistryConstants::STORE_ID => $storeId,
                    RegistryConstants::EMAIL => $email
                ]
            );
        }
    }
}
