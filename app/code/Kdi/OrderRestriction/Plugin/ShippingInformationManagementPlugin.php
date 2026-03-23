<?php

namespace Kdi\OrderRestriction\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Checkout\Model\ShippingInformationManagement as OriginalShippingInformationManagement;

class ShippingInformationManagementPlugin
{
    protected $restrictedZipCodes = ['IM', 'PO30', 'JE1', 'JE2', 'JE3', 'ZE1', 'ZE2', 'ZE3'];
    public function beforeSaveAddressInformation(
        OriginalShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {

        $postcode = $addressInformation->getShippingAddress()->getPostcode();

        foreach ($this->restrictedZipCodes as $restrictedCode) {
           if (strpos((string) $postcode, (string) $restrictedCode) === 0) {
         throw new LocalizedException(__('Shipping to this ZIP code is not allowed.'));
      }

        }

        return [$cartId, $addressInformation];

    }
}
