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
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Model\Import\Behavior;

use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Source\Import\AbstractBehavior;

/**
 * Import behavior source model used for defining the behaviour during the import.
 *
 * @api
 * @since 100.0.2
 */
class Account extends AbstractBehavior
{
    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            Import::BEHAVIOR_APPEND => __('Add/Update'),
            Import::BEHAVIOR_REPLACE => __('Replace'),
            Import::BEHAVIOR_DELETE => __('Delete')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return 'mp_reward_account';
    }
}
