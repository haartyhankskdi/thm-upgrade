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

use Exception;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as Subject;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Psr\Log\LoggerInterface;

/**
 * Class View
 * @package Mageplaza\RewardPointsUltimate\Plugin\Product
 */
class View
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * View constructor.
     *
     * @param HelperData $helperData
     * @param Registry $registry
     * @param LoggerInterface $logger
     * @param Json $jsonSerializer
     * @param RequestInterface $request
     */
    public function __construct(
        HelperData $helperData,
        Registry $registry,
        LoggerInterface $logger,
        Json $jsonSerializer,
        RequestInterface $request
    ) {
        $this->helperData     = $helperData;
        $this->registry       = $registry;
        $this->logger         = $logger;
        $this->jsonSerializer = $jsonSerializer;
        $this->request        = $request;
    }

    /**
     * @param Subject $configurable
     * @param string $result
     * @return string
     */
    public function afterGetJsonConfig(Subject $configurable, string $result): string
    {
        $product    = $configurable->getProduct();
        $jsonConfig = $this->jsonSerializer->unserialize($result);
        $sellpoints = [];

        if ($product->getTypeId() === 'configurable') {
            $_children = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($_children as $child) {
                if ($this->checkIsSellByPoints($child)) {
                    $sellpoints[] = [
                        'id'     => $child->getEntityId(),
                        'points' => $this->helperData->getPointHelper()->format(
                            $child->getMpRewardSellProduct(),
                            false
                        )
                    ];
                    $jsonConfig['sellPoints']  = $sellpoints;
                }
            }
        }

        $jsonConfig['defaultPoints'] = $product->getMpRewardSellProduct() > 0 ?
            $this->helperData->getPointHelper()->format(
                $product->getMpRewardSellProduct(),
                false
            ) : '';

        return $this->jsonSerializer->serialize($jsonConfig);
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    protected function checkIsSellByPoints($product)
    {
        $customerId       = $this->helperData->getCustomerId();
        $customerGroupIds = explode(',', $product->getMpRwCustomerGroup() ?:'');
        try {
            $customerGroupId = $this->helperData->getGroupIdByCustomerId($customerId);
        } catch (Exception $e) {
            $customerGroupId = '0';
        }

        $isEnabled = $product->getMpRwIsActive()
            && $product->getMpRewardSellProduct() > 0
            && in_array($customerGroupId, $customerGroupIds, true)
            && $this->helperData->isEnabled();

        if ($this->request->getFullActionName() === 'catalog_product_view') {
            return $isEnabled;
        }

        return $isEnabled;
    }
}
