<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model;

use Amasty\Storelocator\Api\Data\BusinessHoursExtensionInterface;
use Amasty\Storelocator\Api\Data\BusinessHoursInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Api\ExtensionAttributesFactory;

class BusinessHours extends AbstractSimpleObject implements BusinessHoursInterface
{
    public const WEEKDAY = 'weekday';
    public const IS_OPEN = 'is_open';
    public const OPEN_FROM = 'open_from';
    public const OPEN_TO = 'open_to';
    public const BREAK_FROM = 'break_from';
    public const BREAK_TO = 'break_to';

    /**
     * @var ExtensionAttributesFactory
     */
    private $extensionFactory;

    public function __construct(
        ExtensionAttributesFactory $extensionFactory,
        array $data = []
    ) {
        $this->extensionFactory = $extensionFactory;
        parent::__construct($data);
    }

    public function setWeekday(?string $weekday): void
    {
        $this->setData(self::WEEKDAY, $weekday);
    }

    public function getWeekday(): ?string
    {
        return $this->_get(self::WEEKDAY);
    }

    public function setIsOpen(?bool $isOpen): void
    {
        $this->setData(self::IS_OPEN, $isOpen);
    }

    public function isOpen(): bool
    {
        return (bool) $this->_get(self::IS_OPEN);
    }

    public function setOpenFrom(?string $openFrom): void
    {
        $this->setData(self::OPEN_FROM, $openFrom);
    }

    public function getOpenFrom(): ?string
    {
        return $this->_get(self::OPEN_FROM);
    }

    public function setOpenTo(?string $openTo): void
    {
        $this->setData(self::OPEN_TO, $openTo);
    }

    public function getOpenTo(): ?string
    {
        return $this->_get(self::OPEN_TO);
    }

    public function setBreakFrom(?string $breakFrom): void
    {
        $this->setData(self::BREAK_FROM, $breakFrom);
    }

    public function getBreakFrom(): ?string
    {
        return $this->_get(self::BREAK_FROM);
    }

    public function setBreakTo(?string $breakTo): void
    {
        $this->setData(self::BREAK_TO, $breakTo);
    }

    public function getBreakTo(): ?string
    {
        return $this->_get(self::BREAK_TO);
    }

    public function setExtensionAttributes(BusinessHoursExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }

    public function getExtensionAttributes(): ?BusinessHoursExtensionInterface
    {
        if (!$this->_get(self::EXTENSION_ATTRIBUTES_KEY)) {
            $extensionAttributes = $this->extensionFactory->create(get_class($this));
            $this->setExtensionAttributes($extensionAttributes);
        }

        return $this->_get(self::EXTENSION_ATTRIBUTES_KEY);
    }
}
