<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model;

use Amasty\Storelocator\Api\Data\ScheduleExtensionInterface;
use Amasty\Storelocator\Api\Data\ScheduleInterface;
use Amasty\Storelocator\Model\ResourceModel\Schedule as ScheduleResource;
use Amasty\Storelocator\Model\Schedule\BusinessHours\GetAll;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\Context;

class Schedule extends \Magento\Framework\Model\AbstractExtensibleModel implements ScheduleInterface
{
    public const SCHEDULE = 'schedule';
    public const BUSINESS_HOURS = 'business_hours';

    public const OPEN_TIME = 'from';
    public const CLOSE_TIME = 'to';
    public const BREAK_FROM = 'break_from';
    public const BREAK_TO = 'break_to';
    public const TIME_HOURS = 'hours';
    public const TIME_MINUTES = 'minutes';

    /**
     * @var GetAll
     */
    private $getAllBusinessHours;

    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param GetAll $getAllBusinessHours
     * @param array $data
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        GetAll $getAllBusinessHours,
        array $data = []
    ) {
        $this->getAllBusinessHours = $getAllBusinessHours;
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, null, null, $data);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ScheduleResource::class);
        $this->setIdFieldName('id');
    }

    public function setName(?string $name): void
    {
        $this->setData(self::NAME, $name);
    }

    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    public function setSchedule(?string $schedule): void
    {
        $this->setData(self::SCHEDULE, $schedule);
    }

    public function getSchedule(): ?string
    {
        return $this->getData(self::SCHEDULE);
    }

    public function setBusinessHours(?array $businessHours): void
    {
        $this->setData(self::BUSINESS_HOURS, $businessHours);
    }

    public function getBusinessHours(): array
    {
        if (!$this->getData(self::BUSINESS_HOURS)) {
            $this->setBusinessHours($this->getAllBusinessHours->execute($this));
        }

        return $this->getData(self::BUSINESS_HOURS) ?? [];
    }

    public function setExtensionAttributes(ScheduleExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    public function getExtensionAttributes(): ?ScheduleExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }
}
