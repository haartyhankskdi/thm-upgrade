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

namespace Mageplaza\FrequentlyBought\Api;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface FrequentlyBoughtRepositoryInterface
 * @package Mageplaza\FrequentlyBought\Api
 */
interface FrequentlyBoughtRepositoryInterface
{
    /**
     * @param int|null $storeId
     * @return \Mageplaza\FrequentlyBought\Api\Data\ConfigInterface
     */
    public function config($storeId = null);

    /**
     * Get product links list
     *
     * @param string $sku
     *
     * @return \Mageplaza\FrequentlyBought\Api\Data\ProductLinkInterface[]
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getList($sku);

    /**
     * Save product link
     *
     * @param \Mageplaza\FrequentlyBought\Api\Data\ProductLinkInterface $entity
     *
     * @return bool
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function save(\Mageplaza\FrequentlyBought\Api\Data\ProductLinkInterface $entity);

    /**
     * Delete product link
     *
     * @param \Mageplaza\FrequentlyBought\Api\Data\ProductLinkInterface $entity
     *
     * @return bool
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function delete(\Mageplaza\FrequentlyBought\Api\Data\ProductLinkInterface $entity);

    /**
     * @param string $sku
     * @param string $linkedProductSku
     *
     * @return bool
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function deleteById($sku, $linkedProductSku);
}
