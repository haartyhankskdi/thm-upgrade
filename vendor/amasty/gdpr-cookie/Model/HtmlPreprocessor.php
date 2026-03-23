<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model;

use Magento\Framework\Escaper;

class HtmlPreprocessor
{
    /**
     * @var array
     */
    protected $allowedTags;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        Escaper $escaper,
        array $allowedTags = []
    ) {
        $this->escaper = $escaper;
        $this->allowedTags = $allowedTags;
    }

    public function execute(?string $html): ?string
    {
        return $this->escaper->escapeHtml($html, $this->allowedTags);
    }
}
