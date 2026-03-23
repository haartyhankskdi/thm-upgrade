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

namespace Mageplaza\RewardPointsUltimate\Cron;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\RewardPoints\Model\ResourceModel\Account\Collection;
use Mageplaza\RewardPoints\Model\ResourceModel\Account\CollectionFactory;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;

/**
 * Class UpTier
 * @package Mageplaza\RewardPointsUltimate\Cron
 */
class UpTier
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Birthday constructor.
     *
     * @param HelperData $helperData
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        HelperData $helperData,
        CollectionFactory $collectionFactory
    ) {
        $this->helperData = $helperData;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        if (!$this->helperData->isEnabled()) {
            return $this;
        }

        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        foreach ($collection->getItems() as $account) {
            $this->helperData->updateTier($account->getCustomerId(), $account);
        }

        return $this;
    }
}
