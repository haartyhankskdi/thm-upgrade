<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Anonymization;

use Magento\Framework\Exception\NotFoundException;

class TypePool
{
    /**
     * @var AbstractType[]
     */
    private $types;

    public function __construct(
        array $types
    ) {
        $this->checkConfigInstance($types);
        $this->types = $types;
    }

    /**
     * @param string $type
     * @return AbstractType
     * @throws NotFoundException
     */
    public function get(string $type): AbstractType
    {
        if (!isset($this->types[$type])) {
            throw new NotFoundException(
                __('The "%1" anonymization type isn\'t defined. Verify the type and try again.', $type)
            );
        }

        return $this->types[$type];
    }

    /**
     * @return AbstractType[]
     */
    public function getAll(): array
    {
        return $this->types;
    }

    /**
     * @param array $types
     * @throws \InvalidArgumentException
     * @return void
     */
    private function checkConfigInstance(array $types): void
    {
        foreach ($types as $typeName => $type) {
            if (!$type instanceof AbstractType) {
                throw new \InvalidArgumentException(
                    'The type instance "' . $typeName . '" must be implement ' . AbstractType::class
                );
            }
        }
    }
}
