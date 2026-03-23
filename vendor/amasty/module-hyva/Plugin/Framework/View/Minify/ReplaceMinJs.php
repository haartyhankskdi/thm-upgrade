<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hyva Theme Base Package (System)
 */

namespace Amasty\Hyva\Plugin\Framework\View\Minify;

use Amasty\Hyva\Model\ReplaceJsPath;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\View\Asset\PreProcessor\MinificationConfigProvider;
use Magento\Framework\View\Asset\PreProcessor\Minify;

/**
 * Prepare JS import lines for minified JS
 */
class ReplaceMinJs
{
    /**
     * @var ReplaceJsPath
     */
    private $replaceJsPath;

    /**
     * @var Minification
     */
    private $minification;

    /**
     * @var MinificationConfigProvider
     */
    private $minificationConfig;

    public function __construct(
        ReplaceJsPath $replaceJsPath,
        Minification $minification,
        MinificationConfigProvider $minificationConfig
    ) {
        $this->replaceJsPath = $replaceJsPath;
        $this->minification = $minification;
        $this->minificationConfig = $minificationConfig;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeProcess(Minify $subject, Chain $chain): array
    {
        if (stripos($chain->getOrigAssetPath(), 'amasty') !== false
            && $this->minificationConfig->isMinificationEnabled($chain->getTargetAssetPath())
            && $this->minification->isMinifiedFilename($chain->getTargetAssetPath())
            && !$this->minification->isMinifiedFilename($chain->getOrigAssetPath())
        ) {
            $content = $this->replaceJsPath->replaceImports($chain->getContent());
            $chain->setContent($content);
        }

        return [$chain];
    }
}
