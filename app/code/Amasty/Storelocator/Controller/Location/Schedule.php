<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Controller\Location;

use Amasty\Storelocator\Block\View\Schedule as ScheduleBlock;
use Amasty\Storelocator\Model\Repository\LocationRepository;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;

class Schedule implements HttpGetActionInterface
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @var LocationRepository
     */
    private $locationRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        LayoutFactory $layoutFactory,
        RawFactory $resultRawFactory,
        LocationRepository $locationRepository,
        RequestInterface $request
    ) {
        $this->layout = $layoutFactory->create();
        $this->resultRawFactory = $resultRawFactory;
        $this->locationRepository = $locationRepository;
        $this->request = $request;
    }

    public function execute()
    {
        $result = $this->resultRawFactory->create();

        try {
            $result->setContents($this->getScheduleBlockHtml($this->getLocationId()));

            return $result;
        } catch (NoSuchEntityException $e) {
            $result->setHttpResponseCode(400);
            $result->setContents('');

            return $result;
        }
    }

    /**
     * @throws NoSuchEntityException
     */
    private function getLocationId(): int
    {
        $locationId = $this->request->getParam('location_id', null);

        if (!$locationId) {
            throw new NoSuchEntityException();
        }

        return (int)$locationId;
    }

    /**
     * @throws NoSuchEntityException
     */
    private function getScheduleBlockHtml(int $locationId): string
    {
        $location = $this->locationRepository->getById($locationId);
        $scheduleBlock = $this->layout->createBlock(ScheduleBlock::class, 'amasty_store_locator_schedule');
        $scheduleBlock->setData('location', $location);

        return $scheduleBlock->toHtml();
    }
}
