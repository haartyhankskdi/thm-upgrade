<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Action implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $statusNames = [
            ['label' => __('Delete Request Submitted'), 'value' => 'delete_request_submitted'],
            ['label' => __('Delete Request Approved'), 'value' => 'delete_request_approved'],
            ['label' => __('Delete Request Denied'), 'value' => 'delete_request_denied'],
            ['label' => __('Data Anonymised by Customer'), 'value' => 'data_anonymised_by_customer'],
            ['label' => __('Data Anonymised by Admin'), 'value' => 'data_anonymised_by_admin'],
            ['label' => __('Data Anonymised by Guest'), 'value' => 'data_anonymised_by_guest'],
            ['label' => __('Failed Data Anonymization by Guest'), 'value' => 'data_anonymization_error_by_guest'],
            ['label' => __('Failed Data Anonymization by Customer'), 'value' => 'data_anonymization_error_by_customer'],
            ['label' => __('Personal Data Deleted by Admin'), 'value' => 'data_deleted_by_admin'],
            ['label' => __('Personal Data Downloaded by Customer'), 'value' => 'data_downloaded_by_customer'],
            ['label' => __('Personal Data Downloaded by Guest'), 'value' => 'data_downloaded_by_guest'],
        ];

        return $statusNames;
    }
}
