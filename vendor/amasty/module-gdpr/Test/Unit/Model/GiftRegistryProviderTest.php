<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Test\Unit\Model;

use Amasty\Gdpr\Model\GiftRegistryDataFactory;
use Amasty\Gdpr\Model\GiftRegistryProvider;
use Amasty\Gdpr\Test\Unit\Model\ResourceModel\Entity\Collection as EntityCollection;
use Amasty\Gdpr\Test\Unit\Model\ResourceModel\Person\Collection as PersonCollection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Amasty\Gdpr\Model\GiftRegistryProvider
 */
class GiftRegistryProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GiftRegistryProvider
     */
    private $giftRegistryProvider;

    /**
     * @var GiftRegistryDataFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $giftRegistryDataFactoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->giftRegistryDataFactoryMock = $this->createMock(GiftRegistryDataFactory::class);

        $this->giftRegistryProvider = $objectManager->getObject(
            GiftRegistryProvider::class,
            [
                'giftRegistryDataFactory' => $this->giftRegistryDataFactoryMock
            ]
        );
    }

    /**
     * @param int $size
     * @param bool $expectedResult
     * @dataProvider checkGiftRegistrieDataProvider
     */
    public function testCheckGiftRegistries(int $size, $expectedResult)
    {
        $customerId = 1;
        $entityCollectionMock = $this->createConfiguredMock(
            EntityCollection::class,
            [
                'getSize' => $size
            ]
        );
        $entityCollectionMock->expects($this->once())
            ->method('filterByCustomerId')
            ->willReturn($entityCollectionMock);
        $entityCollectionMock->expects($this->once())
            ->method('filterByActive')
            ->willReturn($entityCollectionMock);
        $this->giftRegistryDataFactoryMock->expects($this->once())
            ->method('create')
            ->with(GiftRegistryDataFactory::GIFT_REGISTRY_ENTITY_KEY)
            ->willReturn($entityCollectionMock);

        $this->assertEquals($expectedResult, $this->giftRegistryProvider->checkGiftRegistries($customerId));
    }

    public function checkGiftRegistrieDataProvider(): array
    {
        return [
            [
                2,
                true
            ],
            [
                0,
                false
            ]
        ];
    }

    public function testGetGiftRegistryEntityCollectionByCustomerId()
    {
        $customerId = 1;
        $entityCollectionMock = $this->createMock(EntityCollection::class);
        $entityCollectionMock->expects($this->once())
            ->method('filterByCustomerId')
            ->willReturn($entityCollectionMock);
        $this->giftRegistryDataFactoryMock->expects($this->once())
            ->method('create')
            ->with(GiftRegistryDataFactory::GIFT_REGISTRY_ENTITY_KEY)
            ->willReturn($entityCollectionMock);

        $this->assertEquals(
            $entityCollectionMock,
            $this->giftRegistryProvider->getGiftRegistryEntityCollectionByCustomerId($customerId)
        );
    }

    public function testGetGiftRegistryPersonCollectionByEntities()
    {
        $giftRegistryEntities = [3];
        $personCollectionMock = $this->createMock(PersonCollection::class);
        $personCollectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->willReturn($personCollectionMock);
        $this->giftRegistryDataFactoryMock->expects($this->once())
            ->method('create')
            ->with(GiftRegistryDataFactory::GIFT_REGISTRY_PERSON_KEY)
            ->willReturn($personCollectionMock);

        $this->assertEquals(
            $personCollectionMock,
            $this->giftRegistryProvider->getGiftRegistryPersonCollectionByEntities($giftRegistryEntities)
        );
    }
}
