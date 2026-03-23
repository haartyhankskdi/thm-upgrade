<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Test\Unit\Block;

use Amasty\Gdpr\Block\Settings;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Amasty\Gdpr\Block\Settings
 */
class SettingsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Settings
     */
    private $settings;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->settings = $objectManager->getObject(Settings::class);
    }

    /**
     * @param string $content
     * @param array $tagsToRemove
     * @param string $expectedResult
     * @dataProvider stripHtmlTagsDataProvider
     */
    public function testStripHtmlTags($content, $tagsToRemove, $expectedResult)
    {
        $result = $this->settings->stripHtmlTags($content, $tagsToRemove);
        $this->assertEquals($expectedResult, $result);
    }

    public function stripHtmlTagsDataProvider(): array
    {
        return [
            [
                '',
                [],
                ''
            ],
            [
                '<html><p>test</p> <b>some text</b></html>',
                ['p', 'B', 'em'],
                '<html>test some text</html>'
            ],
        ];
    }
}
