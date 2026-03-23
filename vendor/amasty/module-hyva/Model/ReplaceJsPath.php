<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hyva Theme Base Package (System)
 */

namespace Amasty\Hyva\Model;

/**
 * @see \Amasty\Hyva\Test\Unit\Model\ReplaceJsPathTest
 */
class ReplaceJsPath
{
    public const PATTERN = '/(?<!\.min)(\.js[\'"])/U';

    /**
     * Replaces .js with .min.js
     */
    public function replaceImports(string $content): string
    {
        return preg_replace(self::PATTERN, '.min$1', $content);
    }
}
