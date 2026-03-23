<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Api\Data;

interface ScheduleInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    public const NAME = 'name';

    /**
     * @param string|null $name
     * @return void
     */
    public function setName(?string $name): void;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param \Amasty\Storelocator\Api\Data\BusinessHoursInterface[]|null $businessHours
     * @return void
     */
    public function setBusinessHours(?array $businessHours): void;

    /**
     * @return \Amasty\Storelocator\Api\Data\BusinessHoursInterface[]
     */
    public function getBusinessHours(): array;

    /**
     * @param \Amasty\Storelocator\Api\Data\ScheduleExtensionInterface $extensionAttributes
     * @return \Amasty\Storelocator\Api\Data\ScheduleInterface
     */
    public function setExtensionAttributes(ScheduleExtensionInterface $extensionAttributes);

    /**
     * @return \Amasty\Storelocator\Api\Data\ScheduleExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ScheduleExtensionInterface;
}
