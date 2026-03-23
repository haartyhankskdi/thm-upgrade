<?php

namespace Ebizmarts\Notification\Test\Unit\Model;

use Ebizmarts\Notification\Model\NotificationRepository;
use Ebizmarts\Notification\Api\Data\Notification\NotificationInterface;
use Ebizmarts\Notification\Api\NotificationRepositoryInterface;
use Ebizmarts\Notification\Model\NotificationFactory as ModelNotificationFactory;
use Ebizmarts\Notification\Model\Notification as ModelNotification;
use Ebizmarts\Notification\Model\ResourceModel\NotificationFactory as ResourceNotificationFactory;
use Ebizmarts\Notification\Model\ResourceModel\Notification as ResourceNotification;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class NotificationRepositoryTest extends TestCase
{
    /** @var ModelNotificationFactory $notificationFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $notificationFactory;

    /** @var ModelNotification $modelNotification|\PHPUnit_Framework_MockObject_MockObject */
    private $modelNotification;

    /** @var LoggerInterface $loggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $loggerInterface;

    /** @var ResourceNotificationFactory $resourceNotificationFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceNotificationFactory;

    /** @var ResourceNotification $resourceNotification|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceNotification;

    /** @var NotificationRepositoryInterface $notificationRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $notificationRepositoryInterface;

    /** @var NotificationInterface $notificationInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $notificationInterface;

    /** @var AbstractDb $abstractDb|\PHPUnit_Framework_MockObject_MockObject */
    private $abstractDb;

    /** @var NotificationRepository $notificationRepository */
    private $notificationRepository;

    public function setUp(): void
    {
        $this->notificationFactory = $this->getMockBuilder(ModelNotificationFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->modelNotification = $this->getMockBuilder(ModelNotification::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerInterface = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceNotificationFactory = $this->getMockBuilder(ResourceNotificationFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceNotification = $this->getMockBuilder(ResourceNotification::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->notificationRepositoryInterface = $this->getMockBuilder(NotificationRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->notificationInterface = $this->getMockBuilder(NotificationInterface::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->abstractDb = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->notificationRepository = new NotificationRepository(
            $this->notificationFactory,
            $this->loggerInterface,
            $this->resourceNotificationFactory
        );
    }

    public function testSave()
    {
        $notificationId = 'opayo1';
        $updatedAt = '2023-03-30 13:59:58';
        $id = 1;

        $this->notificationFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->modelNotification);

        $this->notificationInterface->expects($this->once())
            ->method('getNotificationId')
            ->willReturn($notificationId);

        $this->modelNotification->expects($this->once())
            ->method('setNotificationId')
            ->with($notificationId)
            ->willReturnSelf();

        $this->notificationInterface->expects($this->once())
            ->method('getUpdatedAt')
            ->willReturn($updatedAt);

        $this->modelNotification->expects($this->once())
            ->method('setUpdatedAt')
            ->with($updatedAt)
            ->willReturnSelf();

        $this->notificationInterface->expects($this->once())
            ->method('getId')
            ->willReturn($id);

        $this->modelNotification->expects($this->once())
            ->method('setId')
            ->with($id)
            ->willReturnSelf();

        $this->modelNotification->expects($this->once())
            ->method('getResource')
            ->willReturn($this->abstractDb);

        $this->abstractDb->expects($this->once())
            ->method('save')
            ->with($this->modelNotification)
            ->willReturnSelf();

        $this->notificationRepository->save($this->notificationInterface);
    }

    public function testGetById()
    {
        $id = 1;

        $this->notificationFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->modelNotification);

        $this->modelNotification->expects($this->once())
            ->method('load')
            ->with($id)
            ->willReturnSelf();

        $this->modelNotification->expects($this->once())
            ->method('getId')
            ->willReturn($id);

        $this->notificationRepository->getById($id);
    }

    public function testGetByNotificationId()
    {
        $notificationId = 'opayo1';

        $this->resourceNotificationFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resourceNotification);

        $this->resourceNotification->expects($this->once())
            ->method('getByNotificationId')
            ->with($notificationId)
            ->willReturn([$this->notificationInterface]);

        $this->notificationRepository->getByNotificationId($notificationId);
    }

    public function testDelete()
    {
        $id = 1;

        $this->notificationFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->modelNotification);

        $this->modelNotification->expects($this->once())
            ->method('load')
            ->with($id)
            ->willReturnSelf();

        $this->modelNotification->expects($this->once())
            ->method('getId')
            ->willReturn($id);

        $this->modelNotification->expects($this->once())
            ->method('delete')
            ->willReturnSelf();

        $this->notificationRepository->delete($id);
    }
}
