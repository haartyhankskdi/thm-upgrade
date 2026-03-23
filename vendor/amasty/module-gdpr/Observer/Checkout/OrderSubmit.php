<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Observer\Checkout;

use Amasty\Base\Model\Serializer;
use Amasty\Gdpr\Model\ConsentLogger;
use Amasty\Gdpr\Model\Consent\RegistryConstants;
use Amasty\Gdpr\Observer\Customer\ConfirmedCustomerActions;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Can't use plugin on PaymentInformationManagementInterface because it is only for logged in customers.
 * Rolled back Observer.
 */
class OrderSubmit extends ConfirmedCustomerActions implements ObserverInterface
{
    /**
     * @var ConsentRegistry
     */
    private $consentRegistry;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        ManagerInterface $eventManager,
        ConsentRegistry $consentRegistry,
        Serializer $serializer
    ) {
        parent::__construct($request, $storeManager, $eventManager);
        $this->consentRegistry = $consentRegistry;
        $this->serializer = $serializer;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var OrderInterface $order */
            $order = $observer->getData('order');
            $additionalInfo = $order->getPayment()->getAdditionalInformation();

            if (isset($additionalInfo[RegistryConstants::CONSENTS])) {
                $serializedCodes = $additionalInfo[RegistryConstants::CONSENTS];
                $this->consentRegistry->setConsents($this->serializer->unserialize($serializedCodes));
            }
            $this->processConsentCodes(
                $this->consentRegistry->getConsents(),
                ConsentLogger::FROM_CHECKOUT,
                (int)$order->getCustomerId(),
                (int)$order->getStoreId(),
                $order->getCustomerEmail()
            );
            $this->consentRegistry->resetConsents();
        } catch (\Exception $e) {
            return;
        }
    }
}
