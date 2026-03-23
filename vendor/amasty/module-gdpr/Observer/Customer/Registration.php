<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Observer\Customer;

use Amasty\Gdpr\Model\Consent\RegistryConstants;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Registration extends ConfirmedCustomerActions implements ObserverInterface
{
    /**
     * @param Observer $observer
     *
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        // compatible with Amasty Checkout
        $skip = $observer->getData('amasty_checkout_register');

        if (!$skip) {
            $controller = $observer->getData('account_controller');

            if ($controller && !$controller->getRequest()->getParam(RegistryConstants::CONSENTS)) {
                return;
            }
        }

        parent::execute($observer);
    }
}
