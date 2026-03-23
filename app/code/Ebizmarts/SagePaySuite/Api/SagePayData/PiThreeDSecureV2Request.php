<?php

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Model\AbstractExtensibleModel;

class PiThreeDSecureV2Request extends AbstractExtensibleModel implements PiThreeDSecureV2RequestInterface
{
    /**
     * A Base64 encoded, encrypted message sent back by Issuing Bank to
     * your Terminal URL at the end of the 3D-Authentication process.
     * You will receive this value back from the Issuing Bank in
     * a field called cres (lower case cr), but should be passed to Sage Pay as cRes.
     * @return string
     */
    public function getCres()
    {
        return $this->getData(self::CRES);
    }

    /**
     * @param string $message
     * @return void
     */
    public function setCres($message)
    {
        $this->setData(self::CRES, $message);
    }
    public function __toArray(): array
    {
        return [
            self::CRES => $this->getCres()
        ];
    }
}
