<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Hyva Compatibility M2 by Amasty
 */


namespace Amasty\XsearchHyvaCompatibility\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class SearchHeaders implements OptionSourceInterface
{
    public const HEADER_DEFAULT = '';

    public const HEADER_A = 'a';
    public const HEADER_B = 'b';
    public const HEADER_C = 'c';

    /**
     * Provides header types for form wrapper
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [
            self::HEADER_DEFAULT => __('Default'),
            self::HEADER_A => __('A-clean'),
            self::HEADER_B => __('B-compact'),
            self::HEADER_C => __('C-stacked'),
        ];

        return $options;
    }
}
