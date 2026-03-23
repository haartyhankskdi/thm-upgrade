<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Model\Config\Source;

use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class CustomerGroups
 * @package Mageplaza\RewardPointsUltimate\Model\Config\Source
 */
class CustomerGroups extends AbstractSource
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * CustomerGroups constructor.
     *
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $this->_options[] = [
            'label' => __('-- Please Select --'),
            'value' => '999',
        ];

        foreach ($this->collection->toOptionArray() as $item) {
            $this->_options[] = [
                'label' => __($item['label']),
                'value' => $item['value'],
            ];
        }

        return $this->_options;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options[] = [
            'label' => __('-- Please Select --'),
            'value' => '999',
        ];

        foreach ($this->collection->toOptionArray() as $item) {
            $this->_options[] = [
                'label' => __($item['label']),
                'value' => $item['value'],
            ];
        }

        return $this->_options;
    }
}
