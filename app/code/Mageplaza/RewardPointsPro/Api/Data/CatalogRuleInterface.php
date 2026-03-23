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
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Api\Data;

/**
 * Interface CatalogRuleInterface
 * @package Mageplaza\RewardPointsPro\Api\Data
 */
interface CatalogRuleInterface extends RuleInterface
{
    const POINT_AMOUNT          = 'point_amount';
    const MONEY_STEP            = 'money_step';
    const MAX_POINTS            = 'max_points';

    /**
     * @return int
     */
    public function getPointAmount();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setPointAmount($value);

    /**
     * @return int
     */
    public function getMoneyStep();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setMoneyStep($value);

    /**
     * @return int
     */
    public function getMaxPoints();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setMaxPoints($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Mageplaza\RewardPointsPro\Api\Data\CatalogRuleExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Mageplaza\RewardPointsPro\Api\Data\CatalogRuleExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        \Mageplaza\RewardPointsPro\Api\Data\CatalogRuleExtensionInterface $extensionAttributes
    );
}
