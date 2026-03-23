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

namespace Mageplaza\RewardPointsPro\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPointsPro\Model\Indexer\Rule\RuleProductProcessor;

/**
 * Class Job
 * @package Mageplaza\RewardPointsPro\Model
 */
class Job extends DataObject
{
    /**
     * @var RuleProductProcessor
     */
    protected $ruleProcessor;

    /**
     * Basic object initialization
     *
     * @param RuleProductProcessor $ruleProcessor
     */
    public function __construct(
        RuleProductProcessor $ruleProcessor
    ) {
        $this->ruleProcessor = $ruleProcessor;
    }

    /**
     * Dispatch event "catalog_earning_rule_apply_all" and set success or error message depends on result
     * @return Job
     * @api
     */
    public function applyAll()
    {
        try {
            $this->ruleProcessor->markIndexerAsInvalid();
            $this->setSuccess(__('Updated rules applied.'));
        } catch (LocalizedException $e) {
            $this->setError($e->getMessage());
        }

        return $this;
    }
}
