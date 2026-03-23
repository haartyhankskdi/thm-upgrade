<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\RewardPointsUltimate\Model\ResourceModel\Behavior;

use Mageplaza\RewardPointsPro\Model\ResourceModel\AbstractCollection;
use Mageplaza\RewardPointsUltimate\Api\Data\BehaviorSearchResultInterface;
use Mageplaza\RewardPointsUltimate\Model\Behavior;

/**
 * Class Collection
 * @package Mageplaza\RewardPointsUltimate\Model\ResourceModel\Rate
 */
class Collection extends AbstractCollection implements BehaviorSearchResultInterface
{
    /**
     * @type string
     */
    protected $_idFieldName = 'rule_id';

    /**
     * @var string
     */
    protected $associatedEntityMapVirtual
        = 'Mageplaza\RewardPointsUltimate\Model\ResourceModel\Behavior\AssociatedEntityMap';

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(
            Behavior::class,
            \Mageplaza\RewardPointsUltimate\Model\ResourceModel\Behavior::class
        );
    }
}
