<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Plugin\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPoints\Helper\Point;
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;

/**
 * Class ProductRepository
 * @package Mageplaza\RewardPointsUltimate\Plugin\Product
 */
class ProductRepository
{
    /**
     * @var Point
     */
    protected $pointHelper;

    /**
     * @var BehaviorFactory
     *
     */
    protected $behaviorFactory;

    /**
     * ProductRepository constructor.
     *
     * @param Point $pointHelper
     */
    public function __construct(
        Point $pointHelper,
        BehaviorFactory $behaviorFactory
    ) {
        $this->pointHelper = $pointHelper;
        $this->behaviorFactory = $behaviorFactory;
    }

    /**
     * @param ProductRepositoryInterface $subject
     * @param Product $result
     *
     * @return Product
     * @throws LocalizedException
     */

    public function afterGet(
        ProductRepositoryInterface $subject,
        $result
    ) {
        if (!$this->pointHelper->isEnabled()) {
            return $result;
        }

        $this->addEarningData($result);

        return $result;
    }

    /**
     * @param Product $product
     *
     * @throws LocalizedException
     */
    public function addEarningData($product)
    {
        $extensionAttributes = $product->getExtensionAttributes();
        $pointReview = $this->behaviorFactory->create()->getPointByAction(CustomerEvents::PRODUCT_REVIEW);

        $extensionAttributes->setMpRewardPointReview($pointReview ?: 0);
    }

    /**
     * @param ProductRepositoryInterface $subject
     * @param SearchResults $searchCriteria
     *
     * @return SearchResults
     * @throws LocalizedException
     */
    public function afterGetList(
        ProductRepositoryInterface $subject,
        SearchResults $searchCriteria
    ) {
        if (!$this->pointHelper->isEnabled()) {
            return $searchCriteria;
        }

        /** @var Product $entity */
        foreach ($searchCriteria->getItems() as $entity) {
            $this->addEarningData($entity);
        }

        return $searchCriteria;
    }
}
