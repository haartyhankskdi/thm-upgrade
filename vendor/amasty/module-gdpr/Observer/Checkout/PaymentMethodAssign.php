<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Observer\Checkout;

use Amasty\Base\Model\Serializer;
use Amasty\Gdpr\Model\Consent\RegistryConstants;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PaymentMethodAssign implements ObserverInterface
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
        ConsentRegistry $consentRegistry,
        Serializer $serializer
    ) {
        $this->consentRegistry = $consentRegistry;
        $this->serializer = $serializer;
    }

    public function execute(Observer $observer)
    {
        /** @var DataObject $data **/
        $data = $observer->getData('data');
        /** @var DataObject $paymentModel **/
        $paymentModel = $observer->getData('payment_model');

        if ($data && $paymentModel) {
            $additionalData = $data->getAdditionalData() ?? [];

            if (isset($additionalData[RegistryConstants::CONSENTS])) {
                $serializedCodes = $additionalData[RegistryConstants::CONSENTS];
                $this->consentRegistry->setConsents($this->serializer->unserialize($serializedCodes));
            }
        }
    }
}
