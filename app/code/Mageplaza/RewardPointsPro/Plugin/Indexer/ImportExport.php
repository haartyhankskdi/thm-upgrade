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

namespace Mageplaza\RewardPointsPro\Plugin\Indexer;

use Magento\ImportExport\Model\Import;
use Mageplaza\RewardPointsPro\Model\Indexer\Rule\RuleProductProcessor;

/**
 * Class ImportExport
 * @package Mageplaza\RewardPointsPro\Plugin\Indexer
 */
class ImportExport
{
    /**
     * @var RuleProductProcessor
     */
    protected $ruleProductProcessor;

    /**
     * @param RuleProductProcessor $ruleProductProcessor
     */
    public function __construct(RuleProductProcessor $ruleProductProcessor)
    {
        $this->ruleProductProcessor = $ruleProductProcessor;
    }

    /**
     * Invalidate catalog price rule indexer
     *
     * @param Import $subject
     * @param bool $result
     *
     * @return bool
     */
    public function afterImportSource(Import $subject, $result)
    {
        if (!$this->ruleProductProcessor->isIndexerScheduled()) {
            $this->ruleProductProcessor->markIndexerAsInvalid();
        }

        return $result;
    }
}
