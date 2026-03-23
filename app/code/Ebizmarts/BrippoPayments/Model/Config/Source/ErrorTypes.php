<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Ebizmarts\BrippoPayments\Helper\SoftFailRecover;
use Magento\Framework\Option\ArrayInterface;

class ErrorTypes implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => SoftFailRecover::SOFT_FAIL_RECOVER_ERROR_TYPE_GENERIC, 'label' => __('Generic decline')],
            ['value' => SoftFailRecover::SOFT_FAIL_RECOVER_ERROR_TYPE_3DS, 'label' => __('3DS authentication')],
            ['value' => SoftFailRecover::SOFT_FAIL_RECOVER_ERROR_TYPE_INCORRECT_CVC, 'label' => __('Incorrect CVC')],
            ['value' => SoftFailRecover::SOFT_FAIL_RECOVER_ERROR_TYPE_EXPIRED_CARD, 'label' => __('Expired card')],
            ['value' => SoftFailRecover::SOFT_FAIL_RECOVER_ERROR_TYPE_INSUFFICIENT_FUNDS, 'label' => __('Insufficient funds')],
        ];
    }
}
