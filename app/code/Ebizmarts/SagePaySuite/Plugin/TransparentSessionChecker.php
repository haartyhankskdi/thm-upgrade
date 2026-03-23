<?php

declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Session\SessionStartChecker;

/**
 * Keep current session when the customer is redirected from sagepay to magento
 */
class TransparentSessionChecker
{
    private const TRANSPARENT_REDIRECT_PATH = [
        'elavon/pi/callback3D',
        'elavon/paypal/processing',
        'elavon/pi/createOrderForFailedTransaction'
    ];

    /**
     * @var Http
     */
    private $request;

    /**
     * @param Http $request
     */
    public function __construct(
        Http $request
    ) {
        $this->request = $request;
    }

    /**
     * @param SessionStartChecker $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCheck(SessionStartChecker $subject, bool $result): bool
    {
        if ($result === false) {
            return false;
        }

        foreach (self::TRANSPARENT_REDIRECT_PATH as $path) {
            if (strpos((string)$this->request->getPathInfo(), $path) !== false) {
                return false;
            }
        }

        return true;
    }
}
