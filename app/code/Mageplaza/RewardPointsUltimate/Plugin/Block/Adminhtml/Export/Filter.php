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

namespace Mageplaza\RewardPointsUltimate\Plugin\Block\Adminhtml\Export;

use Magento\Framework\DataObject;
use Magento\ImportExport\Block\Adminhtml\Export\Filter as CoreFilter;
use Mageplaza\RewardPointsUltimate\Model\Export\AttributeCollection;

/**
 * Class Filter
 * @package Mageplaza\RewardPointsUltimate\Plugin\Block\Adminhtml\Export
 */
class Filter
{
    /**
     * @param CoreFilter $subject
     * @param string $columnId
     * @param array|DataObject $column
     *
     * @return array
     */
    public function beforeAddColumn(CoreFilter $subject, $columnId, $column)
    {
        $collection = $subject->getCollection();
        if ($collection instanceof AttributeCollection) {
            $column['filter'] = false;
            $column['sortable'] = false;
        }

        return [$columnId, $column];
    }
}
