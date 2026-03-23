<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml\Customer;

use Amasty\Gdpr\Model\Anonymization\Anonymizer;
use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\GiftRegistryDataFactory;
use Magento\Customer\Controller\AbstractAccount as AbstractAccountAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ProductMetadataInterface;

class Anonymise extends AbstractAccountAction
{
    /**
     * @var Anonymizer
     */
    protected $anonymizer;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var GiftRegistryDataFactory
     */
    private $giftRegistryDataFactory;

    public function __construct(
        Context $context,
        Anonymizer $anonymizer,
        ProductMetadataInterface $productMetadata,
        Config $configProvider,
        GiftRegistryDataFactory $giftRegistryDataFactory
    ) {
        parent::__construct($context);
        $this->anonymizer = $anonymizer;
        $this->productMetadata = $productMetadata;
        $this->configProvider = $configProvider;
        $this->giftRegistryDataFactory = $giftRegistryDataFactory;
    }

    public function execute()
    {
        if ($customerId = (int)$this->_request->getParam('customerId')) {
            try {
                $warningMessage = '';

                if ($notCompletedOrderIds = $this->getNonCompletedOrderIds($customerId)) {
                    $warningMessage = __(
                        'Personal data cannot be anonymised right now, because the customer has '
                        . 'non-completed order(s): %1',
                        implode(' ', $notCompletedOrderIds)
                    );
                }

                if ($this->isCustomerHasActiveGiftRegistry($customerId)) {
                    $warningMessage = __(
                        'Personal data cannot be anonymised right now, because the customer has active Gift Registry.'
                    );
                }

                if ($warningMessage) {
                    $this->messageManager->addWarningMessage($warningMessage);
                } else {
                    $this->anonymizer->anonymizeCustomer($customerId);
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error has occurred: %1', $e->getMessage()));
            }
        }

        return $this->resultRedirectFactory->create()->setRefererUrl();
    }

    protected function isCustomerHasActiveGiftRegistry(int $customerId): bool
    {
        if ($this->productMetadata->getEdition() === 'Enterprise'
            && $this->configProvider->isAvoidGiftRegistryAnonymization()
        ) {
            return (bool)$this->giftRegistryDataFactory->create(GiftRegistryDataFactory::GIFT_REGISTRY_ENTITY_KEY)
                ->filterByCustomerId($customerId)
                ->filterByActive()
                ->getSize();
        }

        return false;
    }

    protected function getNonCompletedOrderIds(int $customerId): array
    {
        return array_map(function ($orderData) {
            return $orderData['increment_id'] ?? '';
        }, $this->anonymizer->getCustomerActiveOrders($customerId));
    }
}
