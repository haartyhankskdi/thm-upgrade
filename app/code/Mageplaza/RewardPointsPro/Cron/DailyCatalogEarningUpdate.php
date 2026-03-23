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

namespace Mageplaza\RewardPointsPro\Cron;

use Mageplaza\RewardPointsPro\Model\Indexer\Rule\RuleProductProcessor;

/**
 * Class DailyCatalogEarningUpdate
 * @package Mageplaza\RewardPointsPro\Cron
 */
class DailyCatalogEarningUpdate
{
    /**
     * @var RuleProductProcessor
     */
    protected $ruleProductProcessor;

    /**
     * DailyCatalogEarningUpdate constructor.
     *
     * @param RuleProductProcessor $ruleProductProcessor
     */
    public function __construct(RuleProductProcessor $ruleProductProcessor)
    {
        $this->ruleProductProcessor = $ruleProductProcessor;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->ruleProductProcessor->markIndexerAsInvalid();
    }
}
