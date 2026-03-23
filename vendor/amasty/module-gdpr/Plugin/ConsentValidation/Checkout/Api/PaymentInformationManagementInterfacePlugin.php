<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Plugin\ConsentValidation\Checkout\Api;

use Amasty\Gdpr\Model\Consent\RegistryConstants;
use Amasty\Gdpr\Model\Consent\Validator;
use Amasty\Gdpr\Model\ConsentLogger;
use Magento\Framework\Exception\LocalizedException;

class PaymentInformationManagementInterfacePlugin
{
    /**
     * @var Validator
     */
    private $validator;

    public function __construct(
        Validator $validator
    ) {
        $this->validator = $validator;
    }

    public function aroundSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Api\PaymentInformationManagementInterface $subject,
        callable $proceed,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        ?\Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $additionalData = $paymentMethod->getAdditionalData();
        $consentData = [];
        if (isset($additionalData[RegistryConstants::CONSENTS])) {
            $consentData = json_decode($additionalData[RegistryConstants::CONSENTS], true);
        }

        if (!$this->validator->validate(ConsentLogger::FROM_CHECKOUT, $consentData)) {
            throw new LocalizedException(__('Policy Confirmation Required'));
        }

        return $proceed($cartId, $paymentMethod, $billingAddress);
    }
}
