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
 * Interface SCSpendingRuleInterface
 * @package Mageplaza\RewardPointsPro\Api\Data
 */
interface SCSpendingRuleInterface extends ShoppingCartRuleInterface
{
    const LABELS = 'store_labels';

    /**
     * Get display label
     *
     * @return \Mageplaza\RewardPointsPro\Api\Data\RuleLabelInterface[]|null
     */
    public function getLabels();

    /**
     * Set display label
     *
     * @param \Mageplaza\RewardPointsPro\Api\Data\RuleLabelInterface[]|null $storeLabels
     * @return $this
     */
    public function setLabels(?array $storeLabels = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Mageplaza\RewardPointsPro\Api\Data\SCSpendingRuleExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Mageplaza\RewardPointsPro\Api\Data\SCSpendingRuleExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        \Mageplaza\RewardPointsPro\Api\Data\SCSpendingRuleExtensionInterface $extensionAttributes
    );
}
