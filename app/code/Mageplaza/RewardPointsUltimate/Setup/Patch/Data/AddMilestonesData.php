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
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
declare(strict_types=1);

namespace Mageplaza\RewardPointsUltimate\Setup\Patch\Data;

use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Store\Model\StoreRepository;
use Mageplaza\RewardPointsUltimate\Model\MilestoneFactory;

/**
 * Class AddMilestonesData
 * @package Mageplaza\RewardPointsUltimate\Setup\Patch\Data
 */
class AddMilestonesData implements
    DataPatchInterface,
    PatchRevertableInterface
{
    /**
     * @var MilestoneFactory
     */
    protected $_milestone;

    /**
     * @var Collection
     */
    protected $customerGroup;

    /**
     * @var StoreRepository
     */
    protected $storeRepository;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * AddMilestonesData constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param MilestoneFactory $_milestone
     * @param Collection $customerGroup
     * @param StoreRepository $storeRepository
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        MilestoneFactory $_milestone,
        Collection $customerGroup,
        StoreRepository $storeRepository,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->_milestone      = $_milestone;
        $this->customerGroup   = $customerGroup;
        $this->storeRepository = $storeRepository;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $customerGroup = [];
        $websiteIds    = [];

        foreach ($this->customerGroup->toOptionArray() as $item) {
            if ($item['value'] !== '0') {
                $customerGroup[] = $item['value'];
            }
        }

        foreach ($this->storeRepository->getList() as $store) {
            $websiteIds[] = $store['website_id'];
        }

        $data = [
            'name'               => __('Base'),
            'status'             => 1,
            'customer_group_ids' => implode(',', $customerGroup),
            'website_ids'        => implode(',', $websiteIds)
        ];
        $post = $this->_milestone->create();
        $post->addData($data)->save();
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.1';
    }
}
