<?php
/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 1/26/17
 * Time: 1:54 PM
 */

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Model\AbstractExtensibleModel;

class PiMerchantSessionKeyRequest extends AbstractExtensibleModel implements PiMerchantSessionKeyRequestInterface
{
    /**
     * @inheritDoc
     */
    public function getVendorName()
    {
        return $this->getData(self::VENDOR_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setVendorName($name)
    {
        $this->setData(self::VENDOR_NAME, $name);
    }
    public function __toArray(): array
    {
        return [self::VENDOR_NAME => $this->getVendorName()];
    }
}
