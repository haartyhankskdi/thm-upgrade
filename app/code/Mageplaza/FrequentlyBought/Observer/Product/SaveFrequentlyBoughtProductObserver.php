<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_FrequentlyBought
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FrequentlyBought\Observer\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\FrequentlyBought\Api\FrequentlyBoughtRepositoryInterface;
use Mageplaza\FrequentlyBought\Helper\Data;
use Mageplaza\FrequentlyBought\Model\ResourceModel\FrequentlyBought;

/**
 * Class SaveFrequentlyBoughtProductObserver
 * @package Mageplaza\FrequentlyBought\Observer\Product
 */
class SaveFrequentlyBoughtProductObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var FrequentlyBought
     */
    protected $resourceModel;

    /**
     * @var FrequentlyBoughtRepositoryInterface
     */
    protected $frequentlyBoughtRepository;

    /**
     * SaveFrequentlyBoughtProductObserver constructor.
     *
     * @param Data $helperData
     * @param FrequentlyBought $resourceModel
     * @param FrequentlyBoughtRepositoryInterface $frequentlyBoughtRepository
     */
    public function __construct(
        Data $helperData,
        FrequentlyBought $resourceModel,
        FrequentlyBoughtRepositoryInterface $frequentlyBoughtRepository
    ) {
        $this->helperData = $helperData;
        $this->resourceModel = $resourceModel;
        $this->frequentlyBoughtRepository = $frequentlyBoughtRepository;
    }

    /**
     * @param Observer $observer
     *
     * @return $this|void
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->helperData->isEnabled()) {
            return $this;
        }
        /** @var Product $product */
        $product = $observer->getProduct();
        $controller = $observer->getController();
        $data = $controller->getRequest()->getPost();
        $linksData = $data['links'];
        $position = 0;
        if ($this->resourceModel->hasProductLinks($product->getId())) {
            foreach ($this->frequentlyBoughtRepository->getList($product->getSku()) as $link) {
                $this->frequentlyBoughtRepository->delete($link);
            }
        }
        if (!isset($linksData['fbt']) || empty($linksData['fbt'])) {
            return $this;
        }
        $fbtList = [];
        // Set array position as a fallback position if necessary
        foreach ($linksData['fbt'] as $item) {
            if (!$this->hasPosition($item)) {
                $item['position'] = ++$position;
            }
            $fbtList[$item['id']] = $item;
        }
        $this->resourceModel->saveProductLinks($product->getId(), $fbtList);

        return $this;
    }

    /**
     * Check if at least one link without position
     *
     * @param array $links
     *
     * @return bool
     */
    protected function hasPosition(array $links)
    {
        if (!array_key_exists('position', $links)) {
            return false;
        }

        return true;
    }
}
