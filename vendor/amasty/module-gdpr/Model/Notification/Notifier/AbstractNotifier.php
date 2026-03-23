<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Notification\Notifier;

use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\Utils\EmailSender;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\SenderResolverInterface;

abstract class AbstractNotifier implements NotifierInterface
{
    /**
     * @var EmailSender
     */
    protected $emailSender;

    /**
     * @var Config
     */
    protected $configProvider;

    /**
     * @var SenderResolverInterface
     */
    protected $senderResolver;

    /**
     * @var CustomerNameGenerationInterface
     */
    protected $nameGeneration;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        EmailSender $emailSender,
        Config $configProvider,
        SenderResolverInterface $senderResolver,
        CustomerNameGenerationInterface $nameGeneration,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->emailSender = $emailSender;
        $this->configProvider = $configProvider;
        $this->senderResolver = $senderResolver;
        $this->nameGeneration = $nameGeneration;
        $this->customerRepository = $customerRepository;
    }

    abstract public function notify(int $customerId, array $vars = []): bool;

    protected function getCustomer(int $customerId): ?CustomerInterface
    {
        try {
            return $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
