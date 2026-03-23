<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Observer\Customer;

use Amasty\Gdpr\Model\Anonymization\AbstractType;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Validator\EmailAddress;

class SessionInit implements ObserverInterface
{
    /**
     * @var EmailAddress
     */
    private $emailValidator;

    public function __construct(EmailAddress $emailValidator)
    {
        $this->emailValidator = $emailValidator;
    }

    public function execute(Observer $observer)
    {
        /** @var Session $customerSession */
        $customerSession = $observer->getData('customer_session');
        $customer = $customerSession->getCustomer();
        $email = $customer->getEmail();

        if ($this->emailValidator->isValid($email)) {
            $emailWithoutDomain = explode('@', $email)[0];

            if ($emailWithoutDomain == AbstractType::ANONYMOUS_SYMBOL) {
                $customerSession->setCustomerId(null);
                $customerSession->destroy(['clear_storage' => false]);
            }
        }
    }
}
