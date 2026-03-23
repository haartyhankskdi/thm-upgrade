<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Test\Unit\Model\DataCollector\Location;

use Amasty\Storelocator\Api\Data\ReviewInterface;
use Amasty\Storelocator\Model\DataCollector\Location\AverageRatingCollector;
use Amasty\Storelocator\Model\Location;
use Amasty\Storelocator\Model\ResourceModel\Review\CollectionFactory;
use Amasty\Storelocator\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use ReflectionException;

/**
 * @see AverageRatingCollector
 */
class AverageRatingCollectorTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @covers AverageRatingCollector::collect
     *
     * @dataProvider collectLocationAverageRatingDataProvider
     *
     * @throws ReflectionException
     */
    public function testCollect(array $ratings, $expectedResult)
    {
        /** @var AverageRatingCollector $averageRatingCollector */
        $averageRatingCollector = $this->createPartialMock(AverageRatingCollector::class, []);

        /** @var Location $locationModel */
        $locationModel = $this->createPartialMock(Location::class, ['getLocationReviews']);
        $reviews = [];
        foreach ($ratings as $rating) {
            /** @var ReviewInterface|MockObject $reviewInterface */
            $reviewRepInterface = $this->createMock(ReviewInterface::class);
            $reviewRepInterface->expects($this->any())->method('getRating')->willReturn($rating);
            $reviews[] = $reviewRepInterface;
        }
        $locationModel->expects($this->any())->method('getLocationReviews')->willReturn($reviews);
        $this->invokeMethod($averageRatingCollector, 'collect', ['location' => $locationModel]);

        $this->assertEquals($expectedResult, $locationModel->getLocationAverageRating());
    }

    /**
     * @return array
     */
    public function collectLocationAverageRatingDataProvider()
    {
        return [
            [[1, 2, 3], 2.0],
            [[1, 5, 2, 3, 3], 2.8],
            [[5, 5, 2, 1, 10], 4.6]
        ];
    }
}
