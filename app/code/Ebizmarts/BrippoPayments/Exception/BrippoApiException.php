<?php

namespace Ebizmarts\BrippoPayments\Exception;

use Magento\Framework\Exception\LocalizedException;

class BrippoApiException extends LocalizedException
{
    public $statusCode;
    public $errorCode;

    public function __construct($msg, $errorCode, $statusCode = 500)
    {
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
        if (is_string($msg)) {
            parent::__construct(__($msg));
        } else {
            parent::__construct($msg);
        }
    }
}
