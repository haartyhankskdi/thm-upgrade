<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Test\Unit\Model\Config\Backend;

use Amasty\Base\Model\AdminNotification\Messages;
use Amasty\Base\Model\Config\Backend\Unsubscribe;
use Amasty\Base\Model\Source\NotificationType;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UnsubscribeTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var TypeListInterface|MockObject
     */
    private $typeListMock;

    /**
     * @var Messages|MockObject
     */
    private $messageManagerMock;

    /**
     * @var NotificationType|MockObject
     */
    private $notificationTypeMock;

    /**
     * @var AbstractResource|MockObject
     */
    private $resourceMock;

    /**
     * @var AbstractDb|MockObject
     */
    private $resourceCollectionMock;

    protected function setUp(): void
    {
        $eventDispatcher = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock->method('getEventDispatcher')->willReturn($eventDispatcher);
        $this->registryMock = $this->createMock(Registry::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->typeListMock = $this->createMock(TypeListInterface::class);
        $this->messageManagerMock = $this->createMock(Messages::class);
        $this->notificationTypeMock = $this->createMock(NotificationType::class);
        $this->notificationTypeMock->method('toOptionArray')->willReturn($this->getTitles());
        $this->resourceMock = $this->createMock(AbstractResource::class);
        $this->resourceCollectionMock = $this->createMock(AbstractDb::class);
    }

    public function testNoChanges(): void
    {
        $model = new Unsubscribe(
            $this->contextMock,
            $this->registryMock,
            $this->scopeConfigMock,
            $this->typeListMock,
            $this->messageManagerMock,
            $this->notificationTypeMock,
            $this->resourceMock,
            $this->resourceCollectionMock
        );
        $this->messageManagerMock->expects($this->never())->method('addMessage');
        $model->afterSave();
    }

    /**
     * @dataProvider processMessageDataProvider
     */
    public function testProcessMessage(string $value, string $expectedMessage): void
    {
        $this->scopeConfigMock->method('getValue')
            ->willReturn(implode(',', [NotificationType::GENERAL, NotificationType::SPECIAL_DEALS]));
        $model = new Unsubscribe(
            $this->contextMock,
            $this->registryMock,
            $this->scopeConfigMock,
            $this->typeListMock,
            $this->messageManagerMock,
            $this->notificationTypeMock,
            $this->resourceMock,
            $this->resourceCollectionMock,
            ['value' => $value]
        );
        $this->messageManagerMock->expects($this->once())->method('addMessage')->with($expectedMessage);
        $model->afterSave();
    }

    private function processMessageDataProvider(): array
    {
        return [
            [
                NotificationType::UNSUBSCRIBE_ALL,
                '<img src="https://feed.amasty.net/news/unsubscribe/unsubscribe_all.svg"/>'
                . '<span>You have successfully unsubscribed from All Notifications.</span>'
            ],
            [
                NotificationType::SPECIAL_DEALS,
                '<img src="https://feed.amasty.net/news/unsubscribe/info.svg"/>'
                . '<span>You have successfully unsubscribed from General Info.</span>'
            ],
        ];
    }

    private function getTitles(): array
    {
        return [
            [
                'value' => NotificationType::UNSUBSCRIBE_ALL,
                'label' => __('Unsubscribe from all')
            ],
            [
                'value' => NotificationType::GENERAL,
                'label' => __('General Info')
            ],
        ];
    }
}
