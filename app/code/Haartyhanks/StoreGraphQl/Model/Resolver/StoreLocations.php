<?php

namespace Haartyhanks\StoreGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Amasty\Storelocator\Api\ScheduleRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Amasty\Storelocator\Model\ResourceModel\Gallery\CollectionFactory as GalleryCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
class StoreLocations implements ResolverInterface
{
    protected $collectionFactory; 
    protected $scheduleRepository;
    protected $timezone;
    protected $galleryCollectionFactory;
    protected $storeManager;

    public function __construct(
        CollectionFactory $collectionFactory,
        ScheduleRepositoryInterface $scheduleRepository,
        TimezoneInterface $timezone,
        GalleryCollectionFactory $galleryCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->scheduleRepository = $scheduleRepository;
        $this->timezone = $timezone;
        $this->galleryCollectionFactory = $galleryCollectionFactory;
        $this->storeManager = $storeManager;

    }

    public function resolve(
        \Magento\Framework\GraphQl\Config\Element\Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', 1);
     $collection->getSelect()->reset(\Magento\Framework\DB\Select::ORDER);
        $collection->setOrder('id', 'DESC');
         //$collection->load();

        $currentDay = strtolower($this->timezone->date()->format('l'));
        $stores = [];

        $counter = 0;

      foreach ($collection as $store) {

    $mediaUrl = $this->storeManager
    ->getStore()
    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

$imageUrl = '';

$galleryCollection = $this->galleryCollectionFactory->create();
$galleryCollection->addFieldToFilter('location_id', $store->getId());
$galleryCollection->setPageSize(1); // sirf first image

$galleryItem = $galleryCollection->getFirstItem();

if ($galleryItem && $galleryItem->getId()) {

    $imageName = $galleryItem->getImageName();

    if ($imageName) {
        $imageUrl = $mediaUrl . 'amasty/amlocator/gallery/'
            . $store->getId() . '/'
            . $imageName;
    }
}


    $businessHoursData = [];
    $workingTimeToday = '';

    if ($store->getSchedule()) {
        try {
            $schedule = $this->scheduleRepository->get($store->getSchedule());

            foreach ($schedule->getBusinessHours() as $hours) {

                $isOpen = $hours->isOpen();

                if ($isOpen && $hours->getWeekday() === $currentDay) {
                    $workingTimeToday = $hours->getOpenFrom() . ' AM - ' . $hours->getOpenTo() . ' PM';
                }

                $businessHoursData[] = [
                    'weekday'   => $hours->getWeekday(),
                    'open_from' => $hours->getOpenFrom() . ' AM',
                    'open_to'   => $hours->getOpenTo() . ' PM',
                    'is_open'   => $isOpen
                ];
            }

        } catch (\Exception $e) {}
    }

    $stores[] = [
        'id' => $store->getId(),
        'name' => $store->getName(),
        'address' => $store->getAddress(),
        'city' => $store->getCity(),
        'state' => $store->getState(),
        'country' => $store->getCountry(),
        'zipcode' => $store->getZip(),
        'phone' => $store->getPhone(),
        'email' => $store->getEmail(),
        'image_url' => $imageUrl,
         'url_key' => $store->getUrlKey(),
        'schedule' => $store->getSchedule(),
        'latitude' => $store->getLat(),
        'longitude' => $store->getLng(),
        'description' => $store->getDescription(),
        'working_time_today' => $workingTimeToday,
        'business_hours' => $businessHoursData,
        'new_store' => ($counter < 3)
    ];

    $counter++;
}

        return $stores;
    }
}