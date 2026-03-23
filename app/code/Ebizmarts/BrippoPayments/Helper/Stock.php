<?php

namespace Ebizmarts\BrippoPayments\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use TypeError;

class Stock extends AbstractHelper
{
    const MSI_MODULE_NAME = 'Magento_Inventory';

    protected $moduleManager;
    protected $stockRegistry;
    protected $objectManager;
    protected $logger;

    /**
     * MSI-related handlers and services (lazy-loaded)
     */
    private $msiHandler;
    private $placeReservationsForSalesEvent;
    private $getSkusByProductIds;
    private $salesChannelFactory;
    private $salesEventFactory;
    private $itemsToSellFactory;
    private $checkItemsQuantity;
    private $stockByWebsiteIdResolver;
    private $getProductTypesBySkus;
    private $isSourceItemManagementAllowedForProductType;
    private $salesEventExtensionFactory;

    /**
     * @param Context $context
     * @param ModuleManager $moduleManager
     * @param StockRegistryInterface $stockRegistry
     * @param ObjectManagerInterface $objectManager
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        ModuleManager $moduleManager,
        StockRegistryInterface $stockRegistry,
        ObjectManagerInterface $objectManager,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->moduleManager = $moduleManager;
        $this->stockRegistry = $stockRegistry;
        $this->objectManager = $objectManager;
        $this->logger = $logger;
    }

    /**
     * @param string $sku
     * @param int $quantity
     * @param int|null $stockId
     * @return bool
     */
    public function isStockAvailable(string $sku, int $quantity, ?int $stockId = 1): bool
    {
        if ($this->isMsiEnabled()) {
            try {
                $this->lazyLoadMsiDependencies();
                return $this->msiHandler->execute($sku, $stockId, $quantity)->isSalable();
            } catch (Exception $e) {
                $this->logger->log('MSI stock availability check failed: ' . $e->getMessage());
                return false;
            }
        }

        try {
            $stockItem = $this->stockRegistry->getStockItemBySku($sku);
            return $stockItem->getQty() >= $quantity && $stockItem->getIsInStock();
        } catch (Exception $e) {
            $this->logger->log('Legacy stock availability check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param OrderInterface $order
     * @return void
     */
    public function updateStock(OrderInterface $order): void
    {
        if ($this->isMsiEnabled()) {
            try {
                $this->appendReservations($order);
            } catch (Exception $ex) {
                $this->logger->log('Error updating stock: ' . $ex->getMessage());
                $this->logger->log($ex->getTraceAsString());
            } catch (TypeError $ex) {
                $this->logger->log('Type Error updating stock: ' . $ex->getMessage());
                $this->logger->log($ex->getTraceAsString());
            }
        }
    }

    /**
     * @return bool
     */
    protected function isMsiEnabled(): bool
    {
        return $this->moduleManager->isEnabled(self::MSI_MODULE_NAME);
    }

    /**
     * @param OrderInterface $order
     * @return void
     * @throws NoSuchEntityException
     */
    protected function appendReservations(OrderInterface $order): void
    {
        $this->lazyLoadMsiDependencies();

        $itemsById = $itemsBySku = $itemsToSell = [];
        foreach ($order->getItems() as $item) {
            $itemsById[$item->getProductId()] = ($itemsById[$item->getProductId()] ?? 0) + $item->getQtyOrdered();
        }
        $productSkus = $this->getSkusByProductIds->execute(array_keys($itemsById));
        $productTypes = $this->getProductTypesBySkus->execute($productSkus);

        foreach ($productSkus as $productId => $sku) {
            if (!$this->isSourceItemManagementAllowedForProductType->execute($productTypes[$sku])) {
                continue;
            }

            $itemsBySku[$sku] = (float)$itemsById[$productId];
            $itemsToSell[] = $this->itemsToSellFactory->create([
                'sku' => $sku,
                'qty' => -(float)$itemsById[$productId]
            ]);
        }

        $websiteId = (int)$order->getStore()->getWebsiteId();

        $salesChannel = $this->salesChannelFactory->create();
        $salesChannel->setType('website');
        $salesChannel->setCode(
            $this->objectManager
                ->get(WebsiteRepositoryInterface::class)
                ->getById($websiteId)
                ->getCode()
        );

        $this->checkItemsQuantity->execute(
            $itemsBySku,
            $this->stockByWebsiteIdResolver->execute($websiteId)->getStockId()
        );

        $salesEvent = $this->salesEventFactory->create([
            'type' => 'order_placed',
            'objectType' => 'order',
            'objectId' => $order->getEntityId(),
        ]);
        $salesEvent->setExtensionAttributes(
            $this->salesEventExtensionFactory->create(['data' => ['objectIncrementId' => $order->getIncrementId()]])
        );

        $this->placeReservationsForSalesEvent->execute($itemsToSell, $salesChannel, $salesEvent);

        $this->logger->logOrderEvent(
            $order,
            'Successfully appended reservation stock.'
        );
    }

    /**
     * @return void
     */
    private function lazyLoadMsiDependencies(): void
    {
        if (!$this->msiHandler) {
            $this->msiHandler = $this->objectManager->get(
                \Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface::class
            );
            $this->placeReservationsForSalesEvent = $this->objectManager->get(
                \Magento\InventorySales\Model\PlaceReservationsForSalesEvent::class
            );
            $this->getSkusByProductIds = $this->objectManager->get(
                \Magento\InventoryCatalog\Model\GetSkusByProductIds::class
            );
            $this->salesChannelFactory = $this->objectManager->get(
                \Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory::class
            );
            $this->salesEventFactory = $this->objectManager->get(
                \Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory::class
            );
            $this->itemsToSellFactory = $this->objectManager->get(
                \Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory::class
            );
            $this->checkItemsQuantity = $this->objectManager->get(
                \Magento\InventorySales\Model\CheckItemsQuantity::class
            );
            $this->stockByWebsiteIdResolver = $this->objectManager->get(
                \Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface::class
            );
            $this->getProductTypesBySkus = $this->objectManager->get(
                \Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface::class
            );
            $this->isSourceItemManagementAllowedForProductType = $this->objectManager->get(
                \Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface::class
            );
            $this->salesEventExtensionFactory = $this->objectManager->get(
                \Magento\InventorySalesApi\Api\Data\SalesEventExtensionFactory::class
            );
        }
    }
}
