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
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\RewardPointsPro\Controller\Points;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\RewardPoints\Helper\Point;
use Mageplaza\RewardPointsPro\Model\CatalogRule;
use Mageplaza\RewardPointsPro\Model\CatalogRuleFactory;

class Ajax extends Action implements HttpGetActionInterface
{

    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @var CatalogRule
     */
    protected $_catalogRule;
    /**
     * @var Point
     */
    protected $_pointHelper;

    /**
     * Ajax constructor.
     *
     * @param Context $context
     * @param CatalogRuleFactory $catalogRuleFactory
     * @param ProductRepository $productRepository
     * @param Point $pointHelper
     */
    public function __construct(
        Context $context,
        CatalogRuleFactory $catalogRuleFactory,
        ProductRepository $productRepository,
        Point $pointHelper
    ) {
        $this->_catalogRule       = $catalogRuleFactory;
        $this->_productRepository = $productRepository;
        $this->_pointHelper       = $pointHelper;

        parent::__construct($context);
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        $productId = $this->_request->getParam('product_id');
        $price     = $this->_request->getParam('price');

        $pointHtml = $this->getPointEarn($this->getProductById($productId), $price);
        $hasError  = !(bool) $pointHtml;

        return $this->getResponse()->representJson(
            AbstractData::jsonEncode(['html' => $pointHtml, 'has_error' => $hasError])
        );
    }

    /**
     * @param $id
     *
     * @return ProductInterface|mixed|null
     * @throws NoSuchEntityException
     */
    public function getProductById($id)
    {
        return $this->_productRepository->getById($id);
    }

    /**
     * @return false|mixed|string
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getPointEarn($product, $price)
    {
        if (!$this->_pointHelper->isEnabled()) {
            return false;
        }
        $product->setPrice($price);
        $pointEarn = $this->_catalogRule->create()->getPointEarnFromRules($product);

        return $pointEarn ? $this->_pointHelper->format($pointEarn) : false;
    }
}
