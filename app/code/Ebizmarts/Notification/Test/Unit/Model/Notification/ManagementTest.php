<?php

namespace Ebizmarts\Notification\Test\Unit\Model\Notification;

use Ebizmarts\Notification\Model\Notification\Management;
use Ebizmarts\Notification\Api\Data\Notification\NotificationInterfaceFactory;
use Ebizmarts\Notification\Api\Data\Notification\NotificationInterface;
use Ebizmarts\Notification\Api\NotificationRepositoryInterfaceFactory;
use Ebizmarts\Notification\Api\NotificationRepositoryInterface;
use Magento\Framework\Notification\NotifierInterface as NotifierPool;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;

class ManagementTest extends TestCase
{
    /** @var NotifierPool $notifierPool|\PHPUnit_Framework_MockObject_MockObject */
    private $notifierPool;

    /** @var NotificationRepositoryInterfaceFactory $notificationRepositoryInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $notificationRepositoryInterfaceFactory;

    /** @var NotificationRepositoryInterface $notificationRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $notificationRepositoryInterface;

    /** @var NotificationInterfaceFactory $notificationInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $notificationInterfaceFactory;

    /** @var NotificationInterface $notificationInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $notificationInterface;

    /** @var LoggerInterface $loggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $loggerInterface;

    /** @var Management $management */
    private $management;

    public function setUp(): void
    {
        $this->notifierPool = $this->getMockBuilder(NotifierPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->notificationRepositoryInterfaceFactory = $this->getMockBuilder(
            NotificationRepositoryInterfaceFactory::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->notificationRepositoryInterface = $this->getMockBuilder(
            NotificationRepositoryInterface::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->notificationInterfaceFactory = $this->getMockBuilder(
            NotificationInterfaceFactory::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->notificationInterface = $this->getMockBuilder(
            NotificationInterface::class
        )->setMethods(['setId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->loggerInterface = $this->getMockBuilder(
            LoggerInterface::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->management = new Management(
            $this->notifierPool,
            $this->notificationRepositoryInterfaceFactory,
            $this->notificationInterfaceFactory,
            $this->loggerInterface
        );
    }

    /**
     * @dataProvider dataProviderNotification
     * @param $dataProvider
     */
    public function testAddNotification($dataProvider)
    {
        $this->notificationRepositoryInterfaceFactory->expects($this->exactly($dataProvider['repositoryExecuteTimes']))
            ->method('create')
            ->willReturnOnConsecutiveCalls(
                $this->notificationRepositoryInterface,
                $this->notificationRepositoryInterface
            );
        $this->notificationRepositoryInterface->expects($this->once())
            ->method('getByNotificationId')
            ->with($dataProvider['id'])
            ->willReturn($dataProvider['saved_notification']);
        $this->notifierPool->expects($this->exactly($dataProvider['majorExecuteTimes']))
            ->method('addMajor')
            ->with(
                $dataProvider['title'],
                $dataProvider['description'],
                $dataProvider['url']
            )
            ->willReturnSelf();
        $this->notifierPool->expects($this->exactly($dataProvider['minorExecuteTimes']))
            ->method('addMinor')
            ->with(
                $dataProvider['title'],
                $dataProvider['description'],
                $dataProvider['url']
            )
            ->willReturnSelf();
        $this->notifierPool->expects($this->exactly($dataProvider['noticeExecuteTimes']))
            ->method('addNotice')
            ->with(
                $dataProvider['title'],
                $dataProvider['description'],
                $dataProvider['url']
            )
            ->willReturnSelf();
        $this->notifierPool->expects($this->exactly($dataProvider['criticalExecuteTimes']))
            ->method('addCritical')
            ->with(
                $dataProvider['title'],
                $dataProvider['description'],
                $dataProvider['url']
            )
            ->willReturnSelf();
        $this->notificationInterfaceFactory->expects($this->exactly($dataProvider['updateDataExecuteTimes']))
            ->method('create')
            ->willReturn($this->notificationInterface);
        $this->notificationInterface->expects($this->exactly($dataProvider['updateDataExecuteTimes']))
            ->method('setId')
            ->with($dataProvider['saved_notification']['id'])
            ->willReturnSelf();
        $this->notificationInterface->expects($this->exactly($dataProvider['updateDataExecuteTimes']))
            ->method('setNotificationId')
            ->with($dataProvider['id'])
            ->willReturnSelf();
        $this->notificationInterface->expects($this->exactly($dataProvider['updateDataExecuteTimes']))
            ->method('setUpdatedAt')
            ->with($dataProvider['updated_at'])
            ->willReturnSelf();
        $this->notificationRepositoryInterface->expects($this->exactly($dataProvider['updateDataExecuteTimes']))
            ->method('save')
            ->with($this->notificationInterface)
            ->willReturnSelf();

        $this->management->addNotification($dataProvider);
    }

    public function dataProviderNotification()
    {
        return [
            'test1' => [
                [
                    'url' => 'https://github.com/ebizmarts/magento2-notification',
                    'severity' => 'Critical',
                    'title' => 'Test Critical Notification',
                    'description' => 'There was an error, which is notified on this notification.',
                    'module' => 'opayo',
                    'id' => 'opayo1',
                    'updated_at' => '2023-03-30 11:09:26',
                    'saved_notification' => [
                        'url' => 'https://github.com/ebizmarts/magento2-notification',
                        'severity' => 'Critical',
                        'title' => 'Test Critical Notification',
                        'description' => 'There was an error, which is notified on this notification.',
                        'module' => 'opayo',
                        'id' => '1',
                        'updated_at' => '2023-03-30 10:59:58'
                    ],
                    'majorExecuteTimes' => 0,
                    'minorExecuteTimes' => 0,
                    'noticeExecuteTimes' => 0,
                    'criticalExecuteTimes' => 1,
                    'repositoryExecuteTimes' => 2,
                    'updateDataExecuteTimes' => 1
                ]
            ],
            'test2' => [
                [
                    'url' => 'https://github.com/ebizmarts/magento2-notification',
                    'severity' => 'Major',
                    'title' => 'Test Major Notification',
                    'description' => 'There was an error, which is notified on this notification.',
                    'module' => 'opayo',
                    'id' => 'opayo2',
                    'updated_at' => '2023-03-30 11:09:26',
                    'saved_notification' => [
                        'url' => 'https://github.com/ebizmarts/magento2-notification',
                        'severity' => 'Major',
                        'title' => 'Test Major Notification',
                        'description' => 'There was an error, which is notified on this notification.',
                        'module' => 'opayo',
                        'id' => '2',
                        'updated_at' => '2023-03-30 10:59:58'
                    ],
                    'majorExecuteTimes' => 1,
                    'minorExecuteTimes' => 0,
                    'noticeExecuteTimes' => 0,
                    'criticalExecuteTimes' => 0,
                    'repositoryExecuteTimes' => 2,
                    'updateDataExecuteTimes' => 1
                ]
            ],
            'test3' => [
                [
                    'url' => 'https://github.com/ebizmarts/magento2-notification',
                    'severity' => 'Minor',
                    'title' => 'Test Minor Notification',
                    'description' => 'There was an error, which is notified on this notification.',
                    'module' => 'opayo',
                    'id' => 'opayo3',
                    'updated_at' => '2023-03-30 11:09:26',
                    'saved_notification' => [
                        'url' => 'https://github.com/ebizmarts/magento2-notification',
                        'severity' => 'Minor',
                        'title' => 'Test Minor Notification',
                        'description' => 'There was an error, which is notified on this notification.',
                        'module' => 'opayo',
                        'id' => '3',
                        'updated_at' => '2023-03-30 10:59:58'
                    ],
                    'majorExecuteTimes' => 0,
                    'minorExecuteTimes' => 1,
                    'noticeExecuteTimes' => 0,
                    'criticalExecuteTimes' => 0,
                    'repositoryExecuteTimes' => 2,
                    'updateDataExecuteTimes' => 1
                ]
            ],
            'test4' => [
                [
                    'url' => 'https://github.com/ebizmarts/magento2-notification',
                    'severity' => 'Notice',
                    'title' => 'Test Notice Notification',
                    'description' => 'There was an error, which is notified on this notification.',
                    'module' => 'opayo',
                    'id' => 'opayo4',
                    'updated_at' => '2023-03-30 11:09:26',
                    'saved_notification' => [
                        'url' => 'https://github.com/ebizmarts/magento2-notification',
                        'severity' => 'Notice',
                        'title' => 'Test Notice Notification',
                        'description' => 'There was an error, which is notified on this notification.',
                        'module' => 'opayo',
                        'id' => '4',
                        'updated_at' => '2023-03-30 10:59:58'
                    ],
                    'majorExecuteTimes' => 0,
                    'minorExecuteTimes' => 0,
                    'noticeExecuteTimes' => 1,
                    'criticalExecuteTimes' => 0,
                    'repositoryExecuteTimes' => 2,
                    'updateDataExecuteTimes' => 1
                ]
            ],
            'test5' => [
                [
                    'url' => 'https://github.com/ebizmarts/magento2-notification',
                    'severity' => 'Notice',
                    'title' => 'Test Notice Notification',
                    'description' => 'There was an error, which is notified on this notification.',
                    'module' => 'opayo',
                    'id' => 'opayo4',
                    'updated_at' => '2023-03-30 11:09:26',
                    'saved_notification' => [
                        'url' => 'https://github.com/ebizmarts/magento2-notification',
                        'severity' => 'Notice',
                        'title' => 'Test Notice Notification',
                        'description' => 'There was an error, which is notified on this notification.',
                        'module' => 'opayo',
                        'id' => '4',
                        'updated_at' => '2023-03-30 11:09:26'
                    ],
                    'majorExecuteTimes' => 0,
                    'minorExecuteTimes' => 0,
                    'noticeExecuteTimes' => 0,
                    'criticalExecuteTimes' => 0,
                    'repositoryExecuteTimes' => 1,
                    'updateDataExecuteTimes' => 0
                ]
            ]
        ];
    }
}
