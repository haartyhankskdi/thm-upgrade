<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Api\Data;

interface BusinessHoursInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @param string|null $weekday
     * @return void
     */
    public function setWeekday(?string $weekday): void;

    /**
     * @return string|null
     */
    public function getWeekday(): ?string;

    /**
     * @param bool|null $isOpen
     * @return void
     */
    public function setIsOpen(?bool $isOpen): void;

    /**
     * @return bool
     */
    public function isOpen(): bool;

    /**
     * @param string|null $openFrom
     * @return void
     */
    public function setOpenFrom(?string $openFrom): void;

    /**
     * @return string|null
     */
    public function getOpenFrom(): ?string;

    /**
     * @param string|null $openTo
     * @return void
     */
    public function setOpenTo(?string $openTo): void;

    /**
     * @return string|null
     */
    public function getOpenTo(): ?string;

    /**
     * @param string|null $breakFrom
     * @return void
     */
    public function setBreakFrom(?string $breakFrom): void;

    /**
     * @return string|null
     */
    public function getBreakFrom(): ?string;

    /**
     * @param string|null $breakTo
     * @return void
     */
    public function setBreakTo(?string $breakTo): void;

    /**
     * @return string|null
     */
    public function getBreakTo(): ?string;

    /**
     * @param \Amasty\Storelocator\Api\Data\BusinessHoursExtensionInterface $extensionAttributes
     * @return \Amasty\Storelocator\Api\Data\BusinessHoursInterface
     */
    public function setExtensionAttributes(BusinessHoursExtensionInterface $extensionAttributes);

    /**
     * @return \Amasty\Storelocator\Api\Data\BusinessHoursExtensionInterface|null
     */
    public function getExtensionAttributes(): ?BusinessHoursExtensionInterface;
}
