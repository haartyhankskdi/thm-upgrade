<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Observer\Checkout;

use Amasty\Gdpr\Model\FlagRegistry;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Compatibility implements ObserverInterface
{
    /**
     * @var FlagRegistry
     */
    private $flagRegistry;

    /**
     * @var array
     */
    protected $blackList = [
        'checkout_index_index'
    ];

    public function __construct(
        FlagRegistry $flagRegistry
    ) {
        $this->flagRegistry = $flagRegistry;
    }

    public function execute(Observer $observer)
    {
        $request = $observer->getRequest();
        $fullActionName = $request->getFullActionName();
        if ($this->isCheckoutIndexAction($request) && !in_array($fullActionName, $this->blackList)) {
            $this->flagRegistry->setFlagEnableSessionPlugin(true);
        }
    }

    /**
     * Check for compatibility with third-party Checkout modules
     *
     * @param Http|RequestInterface $request
     * @return bool
     */
    protected function isCheckoutIndexAction(RequestInterface $request): bool
    {
        $routeName = (string)$request->getRouteName();
        $controllerName = $request->getControllerName();
        $actionName = $request->getActionName();
        if (($controllerName == 'index')
            && ($actionName == 'index')
            && (
                (strpos($routeName, 'checkout') !== false)
                || (strpos($routeName, 'osc') !== false)
            )
        ) {
            return true;
        }

        return false;
    }
}
