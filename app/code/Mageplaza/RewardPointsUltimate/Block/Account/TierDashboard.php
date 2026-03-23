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

namespace Mageplaza\RewardPointsUltimate\Block\Account;

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Element\Template;
use Mageplaza\Core\Helper\Media;
use Mageplaza\RewardPoints\Block\Account\Dashboard;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperUltimate;
use Mageplaza\RewardPointsUltimate\Model\Milestone;
use Mageplaza\RewardPointsUltimate\Model\MilestoneFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\ProgressType;
use Psr\Log\LoggerInterface;

/**
 * Class Subscribe
 * @package Mageplaza\RewardPointsUltimate\Block\Account
 */
class TierDashboard extends Dashboard
{
    /**
     * @var MilestoneFactory
     */
    protected $milestoneFactory;

    /**
     * @var Media
     */
    protected $mediaHelper;

    /**
     * @var HelperUltimate
     */
    protected $ultimateData;

    /**
     * @var AssetRepository
     */
    protected $assetRepo;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * TierDashboard constructor.
     *
     * @param MilestoneFactory $milestoneFactory
     * @param Media $mediaHelper
     * @param Template\Context $context
     * @param Data $helper
     * @param HelperUltimate $ultimateData
     * @param RequestInterface $request
     * @param AssetRepository $assetRepo
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        MilestoneFactory $milestoneFactory,
        Media $mediaHelper,
        Template\Context $context,
        Data $helper,
        HelperUltimate $ultimateData,
        RequestInterface $request,
        AssetRepository $assetRepo,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->milestoneFactory = $milestoneFactory;
        $this->mediaHelper      = $mediaHelper;
        $this->ultimateData     = $ultimateData;
        $this->assetRepo        = $assetRepo;
        $this->request          = $request;
        $this->logger           = $logger;

        parent::__construct($context, $helper, $data);
    }

    /**
     * @return Milestone|mixed
     * @throws LocalizedException
     */
    public function getCurrentTier()
    {
        try {
            return $this->milestoneFactory->create()->loadByCustomerId($this->getAccount()->getCustomerId());
        } catch (Exception $e) {
            return $this->logger->critical($e->getMessage());
        }
    }

    /**
     * @param Milestone $tier
     *
     * @return mixed
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getUpTier($tier)
    {
        $groupId = $this->ultimateData->getGroupIdByCustomerId($this->getAccount()->getCustomerId());

        return $tier->loadUpTier($this->getAccount()->getTotalOrder(), $groupId, $this->ultimateData->getWebsiteId());
    }

    /**
     * @param Milestone $tier
     *
     * @return mixed
     */
    public function getUpPoint($tier)
    {
        $source = $this->ultimateData->getSourceMilestoneAction();

        return $tier->getMinPoint() - $this->getAccount()->getMilestoneTotalEarningPoints(
                $source,
                $this->ultimateData->getPeriodDate()
            );
    }

    /**
     * @param $tier
     *
     * @return string
     */
    public function getImageUrl($tier)
    {
        try {
            $image = $tier->getImage();
            if ($image) {
                $imageUrl = $this->mediaHelper->getMediaUrl('mageplaza/rewardpoints/tier/' . $image);
            } else {
                $imageUrl = $this->helper->getPointHelper()->getIconUrl($this->getStoreId());
            }
        } catch (Exception $e) {
            $imageUrl = '';
        }

        return $imageUrl;
    }

    /**
     * @return bool
     */
    public function isDashboard()
    {
        return true;
    }

    /**
     * @param $text
     *
     * @return string
     */
    public function formatText($text)
    {
        return '<a href="' . $this->getMilestoneUrl() . '">' . $text . '</a>';
    }

    /**
     * @return string
     */
    public function getMilestoneUrl()
    {
        return $this->getUrl('*/milestone/index');
    }

    /**
     * @return array|mixed
     * @throws NoSuchEntityException
     */
    public function iconConfig()
    {
        return $this->helper->getConfigGeneral('show_point_icon', $this->getStoreId());
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->helper->getStore()->getId();
    }
}
