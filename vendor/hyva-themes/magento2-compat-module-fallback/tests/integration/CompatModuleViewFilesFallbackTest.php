<?php declare(strict_types=1);
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2021-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

namespace Hyva\CompatModuleFallback;

use Hyva\CompatModuleFallback\Model\CompatModuleRegistry;
use Hyva\CompatModuleFallback\Plugin\ViewFileOverride as HyvaViewFileOverridePlugin;
use Hyva\Theme\Service\CurrentTheme;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory as DirectoryReadFactory;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Module\Dir as ModuleDir;
use Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Simple as SimpleFallbackResolver;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @covers \Hyva\CompatModuleFallback\Plugin\ViewFileOverride
 */
class CompatModuleViewFilesFallbackTest extends TestCase
{
    private const HYVA_TEST_COMPAT_MODULE = 'Hyva_TestCompatModule';
    private const TEST_MODULE_DIR = __DIR__;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    private $testFileDriver;

    private $testModuleDir;

    protected function setUp(): void
    {
        $this->objectManager = ObjectManager::getInstance();

        /** @var CompatModuleRegistry $compatModuleRegistry */
        $compatModuleRegistry = $this->objectManager->get(CompatModuleRegistry::class);
        $compatModuleRegistry->clear();

        $this->testModuleDir = $this->createTestModuleDir();
        $this->objectManager->addSharedInstance($this->testModuleDir, ModuleDir::class);
        $this->objectManager->removeSharedInstance(HyvaViewFileOverridePlugin::class);

        $this->testFileDriver = $this->createTestFileDriver();

        // Ensure test file driver is used by Magento:
        $this->objectManager->removeSharedInstance(DirectoryReadFactory::class);
        $newDriverPool = new DriverPool([DriverPool::FILE => $this->testFileDriver]);
        $this->objectManager->addSharedInstance($newDriverPool, DriverPool::class);
    }

    /**
     * Create a testing wrapper for the module dir class to allow faking compatibility modules exists.
     *
     * @return ModuleDir
     */
    private function createTestModuleDir(): ModuleDir
    {
        $componentRegistrar = $this->objectManager->get(ComponentRegistrarInterface::class);

        return new class($componentRegistrar) extends ModuleDir {
            private $fakeModulePaths = [];

            public function registerFakeModule(string $fakeModuleName, string $fakeModuleDir): void
            {
                $this->fakeModulePaths[$fakeModuleName] = $fakeModuleDir;
            }

            public function getDir($moduleName, $type = '')
            {
                return $type === '' && isset($this->fakeModulePaths[$moduleName])
                    ? $this->fakeModulePaths[$moduleName]
                    : parent::getDir($moduleName, $type);
            }
        };
    }

    /**
     * Create a testing wrapper for the file system driver to allow faking compatibility module file exists.
     *
     * @return FileDriver
     */
    private function createTestFileDriver(): FileDriver
    {
        return new class() extends FileDriver {
            protected $fakeFiles = [];

            public function forceFileExists(string $file): void
            {
                $this->fakeFiles[] = $file;
            }

            public function isExists($path)
            {
                return array_contains($this->fakeFiles, $path, true)
                    ? true
                    : parent::isExists($path);
            }
        };
    }

    private function setHyvaThemeActive(bool $isHyva): void
    {
        $this->objectManager->removeSharedInstance(CurrentTheme::class);
        $stubThemeService = $this->createMock(CurrentTheme::class);
        $stubThemeService->method('isHyva')->willReturn($isHyva);
        $this->objectManager->addSharedInstance($stubThemeService, CurrentTheme::class);
    }

    private function registerCompatModule(string $originalModule, string $compatModule, string $compatModuleDir): void
    {
        /** @var CompatModuleRegistry $compatModuleRegistry */
        $compatModuleRegistry = $this->objectManager->get(CompatModuleRegistry::class);
        $compatModuleRegistry->register($originalModule, $compatModule);

        $this->testModuleDir->registerFakeModule($compatModule, $compatModuleDir);
    }

    /**
     * Executes the system under test
     *
     * @param string $file
     * @param string $module
     * @return string
     */
    private function runTemplateResolution(string $file, string $module): string
    {
        /** @var SimpleFallbackResolver $sut */
        $sut = $this->objectManager->create(SimpleFallbackResolver::class);

        $type = 'template';
        $area = 'frontend';

        // A stub theme will skip files in themes during fallback which is okay
        // since we are only interested in file resolution within modules
        $theme  = $this->createMock(ThemeInterface::class);
        $locale = null;

        return $sut->resolve($type, $file, $area, $theme, $locale, $module);
    }

    public function testDefaultsToRegularThemeFileIfNoMatchingCompatFile(): void
    {
        $this->setHyvaThemeActive(true);
        $result = $this->runTemplateResolution('html/container.phtml', 'Magento_Theme');

        $this->assertStringEndsWith('magento/module-theme/view/frontend/templates/html/container.phtml', $result);
    }

    public function testDefaultsToRegularThemeFileIfMatchingCompatFileButNoHyvaThemeActive(): void
    {
        $this->setHyvaThemeActive(false);
        $this->registerCompatModule('Magento_Theme', self::HYVA_TEST_COMPAT_MODULE, self::TEST_MODULE_DIR);
        $this->testFileDriver->forceFileExists(self::TEST_MODULE_DIR . '/view/frontend/templates/html/container.phtml');

        $this->assertSame(
            $this->createTestModuleDir()->getDir('Magento_Theme') . '/view/frontend/templates/html/container.phtml',
            $this->runTemplateResolution('html/container.phtml', 'Magento_Theme')
        );
    }

    public function testSingleCompatModuleViewFileTakesPrecedence(): void
    {
        $this->setHyvaThemeActive(true);
        $this->registerCompatModule('Magento_Theme', self::HYVA_TEST_COMPAT_MODULE, self::TEST_MODULE_DIR);
        $this->testFileDriver->forceFileExists(self::TEST_MODULE_DIR . '/view/frontend/templates/html/container.phtml');

        $this->assertSame(
            self::TEST_MODULE_DIR . '/view/frontend/templates/html/container.phtml',
            $this->runTemplateResolution('html/container.phtml', 'Magento_Theme')
        );
    }

    public function testMultipleCompatModuleViewFileTakesFirstCompatFilePresentInModuleLoadOrder(): void
    {
        $this->setHyvaThemeActive(true);

        $compatModuleA    = self::HYVA_TEST_COMPAT_MODULE . 'A';
        $compatModuleB    = self::HYVA_TEST_COMPAT_MODULE . 'B';
        $compatModuleDirA = self::TEST_MODULE_DIR . 'A';
        $compatModuleDirB = self::TEST_MODULE_DIR . 'B';

        $this->registerCompatModule('Magento_Theme', $compatModuleA, $compatModuleDirA);
        $this->registerCompatModule('Magento_Theme', $compatModuleB, $compatModuleDirB);

        $this->testFileDriver->forceFileExists($compatModuleDirA . '/view/frontend/templates/html/container.phtml');
        $this->testFileDriver->forceFileExists($compatModuleDirB . '/view/frontend/templates/html/container.phtml');

        $this->assertSame(
            $compatModuleDirA . '/view/frontend/templates/html/container.phtml',
            $this->runTemplateResolution('html/container.phtml', 'Magento_Theme')
        );
    }

    public function testMultipleCompatModuleTakesFallsBackToSecondCompatModule(): void
    {
        $this->setHyvaThemeActive(true);

        $compatModuleA    = self::HYVA_TEST_COMPAT_MODULE . 'A';
        $compatModuleB    = self::HYVA_TEST_COMPAT_MODULE . 'B';
        $compatModuleDirA = self::TEST_MODULE_DIR . 'A';
        $compatModuleDirB = self::TEST_MODULE_DIR . 'B';

        $this->registerCompatModule('Magento_Theme', $compatModuleA, $compatModuleDirA);
        $this->registerCompatModule('Magento_Theme', $compatModuleB, $compatModuleDirB);

        $this->testFileDriver->forceFileExists($compatModuleDirB . '/view/frontend/templates/html/container.phtml');

        $this->assertSame(
            $compatModuleDirB . '/view/frontend/templates/html/container.phtml',
            $this->runTemplateResolution('html/container.phtml', 'Magento_Theme')
        );
    }

    public function testCompatModuleViewInOrigModuleDirFileTakesPrecedence(): void
    {
        $this->setHyvaThemeActive(true);
        $this->registerCompatModule('Magento_Theme', self::HYVA_TEST_COMPAT_MODULE, self::TEST_MODULE_DIR);
        $this->testFileDriver->forceFileExists(self::TEST_MODULE_DIR . '/view/frontend/templates/Magento_Theme/html/container.phtml');
        $this->testFileDriver->forceFileExists(self::TEST_MODULE_DIR . '/view/frontend/templates/html/container.phtml');

        $this->assertSame(
            self::TEST_MODULE_DIR . '/view/frontend/templates/Magento_Theme/html/container.phtml',
            $this->runTemplateResolution('html/container.phtml', 'Magento_Theme')
        );
    }
}
