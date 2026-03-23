<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Microsoft Consent Mode
 */

namespace Amasty\MicrosoftConsentMode\Plugin\Framework\View\Page\Config\Renderer;

use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Page\Config\Renderer;

class AddConsentScript
{
    public function __construct(
        private readonly LayoutInterface $layout
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRenderHeadContent(Renderer $subject, string $result): string
    {
        $consentBlock = $this->layout->getBlock('amasty.consent.microsoft');

        if (!$consentBlock) {
            return $result;
        }

        return $result . $consentBlock->toHtml();
    }
}
