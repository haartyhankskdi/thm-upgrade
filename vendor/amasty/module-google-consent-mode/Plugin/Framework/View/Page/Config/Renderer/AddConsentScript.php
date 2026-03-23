<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Google Consent Mode
 */

namespace Amasty\GoogleConsentMode\Plugin\Framework\View\Page\Config\Renderer;

use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Page\Config\Renderer;

class AddConsentScript
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    public function __construct(
        LayoutInterface $layout
    ) {
        $this->layout = $layout;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRenderHeadContent(Renderer $subject, string $result): string
    {
        $consentBlock = $this->layout->getBlock('amasty.consent.gtm');

        if (!$consentBlock) {
            return $result;
        }

        return $result . $consentBlock->toHtml();
    }
}
