<?php declare(strict_types=1);
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2021-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

namespace Hyva\CompatModuleFallback\Model;

use PHPUnit\Framework\TestCase;

/**
 * @covers CompatModuleRegistry
 */
class CompatModuleRegistryTest extends TestCase
{
    public function testReturnsConstructorArguments(): void
    {
        $args = [
            ['original_module' => 'Foo_Bar', 'compat_module' => 'Hyva_FooBar'],
            ['original_module' => 'Qux_Baz', 'compat_module' => 'Hyva_QuxBaz'],
            ['original_module' => 'Qux_Baz', 'compat_module' => 'Hyva_QuxBaz2'],
        ];

        $sut = new CompatModuleRegistry($args);

        $this->assertSame([], $sut->getCompatModulesFor('Not_Here'));
        $this->assertSame(['Hyva_FooBar'], $sut->getCompatModulesFor('Foo_Bar'));
        $this->assertSame(['Hyva_QuxBaz', 'Hyva_QuxBaz2'], $sut->getCompatModulesFor('Qux_Baz'));
    }

    public function testReturnsRegisteredModules(): void
    {
        $args = [
            ['original_module' => 'Foo_Bar', 'compat_module' => 'Hyva_FooBar'],
        ];

        $sut = new CompatModuleRegistry($args);

        $sut->register('Foo_Bar', 'Hyva_FooBar2');
        $sut->register('Qux_Baz', 'Hyva_QuxBaz');

        $this->assertSame(['Hyva_FooBar', 'Hyva_FooBar2'], $sut->getCompatModulesFor('Foo_Bar'));
        $this->assertSame(['Hyva_QuxBaz'], $sut->getCompatModulesFor('Qux_Baz'));
    }

    public function testClearsRegisteredModules(): void
    {
        $args = [
            ['original_module' => 'Foo_Bar', 'compat_module' => 'Hyva_FooBar'],
        ];

        $sut = new CompatModuleRegistry($args);
        $sut->register('Qux_Baz', 'Hyva_QuxBaz');

        $sut->clear();

        $this->assertSame([], $sut->getCompatModulesFor('Foo_Bar'));
        $this->assertSame([], $sut->getCompatModulesFor('Qux_Baz'));
        $this->assertSame([], $sut->getCompatModulesFor('Never_There'));
    }

    public function testReturnsAllCompatModules(): void
    {
        $args = [
            ['original_module' => 'Foo_Bar', 'compat_module' => 'Hyva_FooBar'],
            ['original_module' => 'Foo_Bar', 'compat_module' => 'Hyva_FooBar2'],
            ['original_module' => 'Bar_Qux', 'compat_module' => 'Hyva_BarQux'],
        ];

        $sut = new CompatModuleRegistry($args);
        $this->assertSame(['Hyva_FooBar', 'Hyva_FooBar2', 'Hyva_BarQux'], $sut->getCompatModules());
    }

    public function testReturnsNoCompatModulesIfThereAreNone(): void
    {
        $args = [];

        $sut = new CompatModuleRegistry($args);
        $this->assertSame([], $sut->getCompatModules());
    }

    public function testReturnsAllOriginalModules(): void
    {
        $args = [
            ['original_module' => 'Foo_Bar', 'compat_module' => 'Hyva_FooBar'],
            ['original_module' => 'Foo_Bar', 'compat_module' => 'Hyva_FooBar2'],
            ['original_module' => 'Bar_Qux', 'compat_module' => 'Hyva_BarQux'],
        ];

        $sut = new CompatModuleRegistry($args);
        $this->assertSame(['Foo_Bar', 'Bar_Qux'], $sut->getOrigModules());
    }
}
