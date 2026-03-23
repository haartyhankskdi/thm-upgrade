<?php

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

interface PiMerchantSessionKeyRequestInterface
{
    public const VENDOR_NAME = 'vendorName';

    /**
     * Your Sage Pay vendor name.
     * @return string
     */
    public function getVendorName();

    /**
     * @param string $name
     * @return void
     */
    public function setVendorName($name);
}
