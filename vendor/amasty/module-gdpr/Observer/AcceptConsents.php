<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Observer;

use Amasty\Gdpr\Model\Consent;
use Amasty\Gdpr\Model\ConsentLogger;
use Amasty\Gdpr\Model\Consent\RegistryConstants;
use Amasty\Gdpr\Model\Consent\ResourceModel\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class AcceptConsents implements ObserverInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var ConsentLogger
     */
    private $consentLogger;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        Session $session,
        ConsentLogger $consentLogger,
        CollectionFactory $collectionFactory
    ) {
        $this->session = $session;
        $this->collectionFactory = $collectionFactory;
        $this->consentLogger = $consentLogger;
    }

    /**
     * @param Observer $observer
     *
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $codes = $observer->getData(RegistryConstants::CONSENTS) ?: [];
        $from = $observer->getData(RegistryConstants::CONSENT_FROM);
        $customerId = (int)$observer->getData(RegistryConstants::CUSTOMER_ID)
            ?: (int)$this->session->getCustomerId();
        $storeId = $observer->getData(RegistryConstants::STORE_ID);
        $email = $observer->getData(RegistryConstants::EMAIL);
        $consentsCollection = $this->collectionFactory
            ->create()
            ->addStoreData($storeId)
            ->addFieldToFilter(Consent\Consent::CONSENT_CODE, ['in' => array_keys($codes)])
            ->addFieldToFilter(Consent\ConsentStore\ConsentStore::LOG_THE_CONSENT, true)
            ->addFieldToFilter(Consent\ConsentStore\ConsentStore::IS_ENABLED, true);

        /** @var Consent\Consent $consent */
        foreach ($consentsCollection as $consent) {
            $action = (bool)$codes[$consent->getConsentCode()];
            $consent->setIsConsentAccepted($action);
            $this->consentLogger->log(
                $customerId,
                $from,
                $consent,
                $email
            );
        }
    }
}
