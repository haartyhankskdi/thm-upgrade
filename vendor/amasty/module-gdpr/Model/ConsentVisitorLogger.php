<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Model\VisitorConsentLog\ResourceModel\VisitorConsentLog as VisitorConsentLogResource;
use Amasty\Gdpr\Model\VisitorConsentLog\VisitorConsentLog;
use Amasty\Gdpr\Model\VisitorConsentLog\VisitorConsentLogFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class ConsentVisitorLogger
{
    /**
     * @var VisitorConsentLogFactory
     */
    private $visitorConsentLogFactory;

    /**
     * @var VisitorConsentLogResource
     */
    private $visitorConsentLogResource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Visitor
     */
    private $visitor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        VisitorConsentLogFactory $visitorConsentLogFactory,
        VisitorConsentLogResource $visitorConsentLogResource,
        StoreManagerInterface $storeManager,
        Visitor $visitor,
        ActionLogger $logger
    ) {
        $this->visitorConsentLogFactory = $visitorConsentLogFactory;
        $this->visitorConsentLogResource = $visitorConsentLogResource;
        $this->storeManager = $storeManager;
        $this->visitor = $visitor;
        $this->logger = $logger;
    }

    public function log(string $policyVersion, ?int $customerId, ?string $sessionId)
    {
        try {
            /** @var VisitorConsentLog $visitorConsentLog */
            $visitorConsentLog = $this->visitorConsentLogFactory->create();
            $websiteId = $this->storeManager->getWebsite()->getId();
            $data = [
                VisitorConsentLog::CUSTOMER_ID => $customerId ? (int)$customerId : null,
                VisitorConsentLog::SESSION_ID => $sessionId ? (string)$sessionId : null,
                VisitorConsentLog::POLICY_VERSION => $policyVersion,
                VisitorConsentLog::WEBSITE_ID => $websiteId ? (int)$websiteId : null,
                VisitorConsentLog::IP => $this->visitor->getRemoteIp()
            ];
            $visitorConsentLog->addData($data);
            $this->visitorConsentLogResource->save($visitorConsentLog);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
    }
}
