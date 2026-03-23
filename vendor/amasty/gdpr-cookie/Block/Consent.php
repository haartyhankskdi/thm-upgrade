<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Block;

use Amasty\GdprCookie\Model\Layout\LayoutProcessorInterface;
use Magento\Framework\View\Element\Template;

class Consent extends Template
{
    /**
     * @var LayoutProcessorInterface[]
     */
    private $layoutProcessors;

    public function __construct(
        Template\Context $context,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->layoutProcessors = $layoutProcessors;
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout'])
            ? $data['jsLayout']
            : [];
    }

    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }

        return $this->jsLayout;
    }

    public function _resetState(): void
    {
        $this->jsLayout = [];
    }
}
