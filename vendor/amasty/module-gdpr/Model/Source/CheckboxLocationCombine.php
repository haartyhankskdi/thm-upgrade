<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;

class CheckboxLocationCombine implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $optionGroups;

    public function __construct(array $optionGroups)
    {
        $this->optionGroups = $optionGroups;
    }

    public function toOptionArray()
    {
        if (empty($this->optionGroups)) {
            return [];
        }

        $result = [];

        foreach ($this->optionGroups as $optionGroup) {
            if (empty($optionGroup['optionSources'])) {
                continue;
            }

            if (empty($optionGroup['name'])) {
                throw new LocalizedException(__('Checkbox Location Option Group has empty name'));
            }

            $group = [];
            foreach ($optionGroup['optionSources'] as $optionSourceCode => $optionSource) {
                if (!is_subclass_of($optionSource, OptionSourceInterface::class)) {
                    throw new LocalizedException(
                        __('Option Source with code %1 not implements OptionSourceInterface', $optionSourceCode)
                    );
                }
                $group[] = $optionSource->toOptionArray();
            }
            //phpcs:ignore
            $result[] = ['label' => __($optionGroup['name']), 'value' => array_merge([], ...$group)];
        }

        return $result;
    }
}
