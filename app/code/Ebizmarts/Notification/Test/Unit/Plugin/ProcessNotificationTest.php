<?php

namespace Ebizmarts\Notification\Test\Unit\Plugin;

use Ebizmarts\Notification\Plugin\ProcessNotification;
use Ebizmarts\Notification\Model\File\Management as FilesManagement;
use Ebizmarts\Notification\Model\Module\Management as ModuleManagement;
use Ebizmarts\Notification\Model\Notification\Management as NotificationManagement;
use Magento\Backend\Model\Auth;
use Magento\Framework\App\Filesystem\DirectoryList;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ProcessNotificationTest extends TestCase
{
    /** @var FilesManagement $filesManagement|\PHPUnit_Framework_MockObject_MockObject */
    private $filesManagement;

    /** @var NotificationManagement $notificationManagement|\PHPUnit_Framework_MockObject_MockObject */
    private $notificationManagement;

    /** @var ModuleManagement $moduleManagement|\PHPUnit_Framework_MockObject_MockObject */
    private $moduleManagement;

    /** @var LoggerInterface $loggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $loggerInterface;

    /** @var Auth $auth|\PHPUnit_Framework_MockObject_MockObject */
    private $auth;

    /** @var ProcessNotification $plugin */
    private $plugin;

    public function setUp(): void
    {
        $this->filesManagement = $this->getMockBuilder(FilesManagement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->notificationManagement = $this->getMockBuilder(NotificationManagement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->moduleManagement = $this->getMockBuilder(ModuleManagement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerInterface = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->auth = $this->getMockBuilder(Auth::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new ProcessNotification(
            $this->filesManagement,
            $this->notificationManagement,
            $this->moduleManagement,
            $this->loggerInterface
        );
    }

    /**
     * @dataProvider dataProviderProcessNotificationFiles
     * @param $dataProvider
     */
    public function testAfterLogin($dataProvider)
    {
        $this->filesManagement->expects($this->once())
            ->method('getMagentoPath')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($dataProvider['magentoPath']);

        $this->filesManagement->expects($this->once())
            ->method('getAllFiles')
            ->with($dataProvider['resultPath'])
            ->willReturn($dataProvider['allFiles']);

        $this->plugin->afterLogin($this->auth);
    }

    public function dataProviderProcessNotificationFiles()
    {
        return [
            'test1' => [
                [
                    'magentoPath' => '/var/www/html/opayo245/var',
                    'resultPath' => '/var/www/html/opayo245/var/notifications/',
                    'allFiles' => [
                        '/var/www/html/opayo245/var/notifications/mailchimp.xml',
                        '/var/www/html/opayo245/var/notifications/opdayo.xml',
                        '/var/www/html/opayo245/var/notifications/test.xml',
                        '/var/www/html/opayo245/var/notifications/pos.xml',
                        '/var/www/html/opayo245/var/notifications/brippo.xml'
                    ]
                ]
            ],
            'test2' => [
                [
                    'magentoPath' => '/var/www/html/opayo245/var',
                    'resultPath' => '/var/www/html/opayo245/var/notifications/',
                    'allFiles' => [
                    ]
                ]
            ],
            'test3' => [
                [
                    'magentoPath' => '',
                    'resultPath' => '/notifications/',
                    'allFiles' => [
                    ]
                ]
            ]
        ];
    }
}
