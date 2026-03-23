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
 * @category  Mageplaza
 * @package   Mageplaza_FrequentlyBought
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FrequentlyBought\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\FrequentlyBought\Model\ResourceModel\FrequentlyBought as ResourceModel;

/**
 * Class Data
 *
 * @package Mageplaza\FrequentlyBought\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'frequentlybought';

    /**
     * @var ResourceModel
     */
    protected $resourceModel;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param ResourceModel $resourceModel
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        ResourceModel $resourceModel
    ) {
        parent::__construct($context, $objectManager, $storeManager);
        $this->resourceModel = $resourceModel;
    }

    /**
     * @return Media
     */
    public function getMediaHelper()
    {
        return $this->objectManager->get(Media::class);
    }

    /**
     * Get Separator Image Config
     *
     * @return string
     */
    public function getIcon()
    {
        $icon = $this->getConfigGeneral('separator_image');
        if (!$icon) {
            return false;
        }

        return $this->getMediaHelper()->resizeImage($icon, 30);
    }

    /**
     * @param $productId
     *
     * @return bool
     * @throws LocalizedException
     */
    public function hasProductLinks($productId)
    {
        return $this->resourceModel->hasProductLinks($productId);
    }

    /**
     * @param $productId
     *
     * @return array
     * @throws LocalizedException
     */
    public function getChildrenIds($productId)
    {
        return $this->resourceModel->getChildrenIds($productId);
    }

    /**
     * Get method of choosing product to show
     *
     * @return array
     */
    public function getProductMethod()
    {
        $method = $this->getConfigGeneral('product_method');
        if (!is_array($method)) {
            $method = explode(',', $method);
        }

        return $method;
    }

    /**
     * @return mixed
     */
    public function usePopup()
    {
        return $this->getConfigGeneral('use_popup');
    }

    /**
     * @return mixed
     */
    public function getDisplayStyle()
    {
        return $this->getConfigGeneral('display_style');
    }

    /**
     * @return int
     */
    public function getNumberOfProductOnSlider()
    {
        return (int)$this->getConfigGeneral('number_of_item_on_slider');
    }
}
