<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hyva Theme Base Package (System)
 */

namespace Amasty\Hyva\Test\Unit\Model;

use Amasty\Hyva\Model\ReplaceJsPath;
use PHPUnit\Framework\TestCase;

/**
 * @covers ReplaceJsPath
 */
class ReplaceJsPathTest extends TestCase
{
    /**
     * @var ReplaceJsPath
     */
    private $testSubject;

    public function setUp(): void
    {
        $this->testSubject = new ReplaceJsPath();
    }

    /**
     * @dataProvider jsLinesProvider
     */
    public function testReplaceImports(string $content, string $expected): void
    {
        $result = $this->testSubject->replaceImports($content);
        $this->assertSame($expected, $result);
    }

    public function jsLinesProvider(): array
    {
        return [
            'inline' => [
                "import { AddToCartButton } from './components/AddToCartButton.js';" .
                " import { AddToWishlistButton } from './components/AddToWishlistButton.js';",
                "import { AddToCartButton } from './components/AddToCartButton.min.js';" .
                " import { AddToWishlistButton } from './components/AddToWishlistButton.min.js';"
            ],
            'multiline' => [
                "import { AddToCartButton } from './components/AddToCartButton.js';\n
                 import { AddToWishlistButton } from './components/AddToWishlistButton.js';",
                "import { AddToCartButton } from './components/AddToCartButton.min.js';\n
                 import { AddToWishlistButton } from './components/AddToWishlistButton.min.js';"
            ],
            'with_min' => [
                "import { AddToCartButton } from './components/AddToCartButton.min.js';",
                "import { AddToCartButton } from './components/AddToCartButton.min.js';"
            ],
            'js_in_context' => [
                "import { AddToCartButton } from './AddToCartButton.jss';",
                "import { AddToCartButton } from './AddToCartButton.jss';"
            ],
            'quotes' => [
                'import { AddToCartButton } from "./asdasd/AddToCartButton.js";',
                'import { AddToCartButton } from "./asdasd/AddToCartButton.min.js";'
            ],
            'name_with_dots' => [
                "import { AddToCartButton } from './.folder/_sub-folder/super-file.name.js';",
                "import { AddToCartButton } from './.folder/_sub-folder/super-file.name.min.js';"
            ]
        ];
    }
}
