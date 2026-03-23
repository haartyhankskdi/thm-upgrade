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

namespace Mageplaza\FrequentlyBought\Controller\Wishlist;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data;
use Mageplaza\FrequentlyBought\Helper\Data as FbtData;

/**
 * Class Add
 *
 * @package Mageplaza\FrequentlyBought\Controller\Wishlist
 */
class Add extends \Magento\Wishlist\Controller\Index\Add
{
    /**
     * @var FbtData
     */
    protected $fbtDataHelper;

    /**
     * @var Data
     */
    protected $wishListDataHelper;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param Session $customerSession
     * @param WishlistProviderInterface $wishlistProvider
     * @param ProductRepositoryInterface $productRepository
     * @param Validator $formKeyValidator
     * @param Data $wishListDataHelper
     * @param FbtData $fbtDataHelper
     */
    public function __construct(
        Action\Context $context,
        Session $customerSession,
        WishlistProviderInterface $wishlistProvider,
        ProductRepositoryInterface $productRepository,
        Validator $formKeyValidator,
        Data $wishListDataHelper,
        FbtData $fbtDataHelper
    ) {
        $this->wishListDataHelper = $wishListDataHelper;
        $this->fbtDataHelper = $fbtDataHelper;

        parent::__construct(
            $context,
            $customerSession,
            $wishlistProvider,
            $productRepository,
            $formKeyValidator
        );
    }

    /**
     * Adding new item
     *
     * @return Redirect
     * @throws NotFoundException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->fbtDataHelper->isEnabled()) {
            return $resultRedirect->setPath('*/*/');
        }

        $wishList = $this->wishlistProvider->getWishlist();
        if (!$wishList) {
            throw new NotFoundException(__('Page not found.'));
        }

        $session = $this->_customerSession;
        $params = $this->getRequest()->getParams();

        if ($session->getBeforeWishlistRequest()) {
            $params = $session->getBeforeWishlistRequest();
            $session->unsBeforeWishlistRequest();
        }

        try {
            if (!empty($params['mageplaza_fbt'])) {
                $productsName = [];
                foreach ($params['mageplaza_fbt'] as $productId => $value) {
                    $requestParams = [];
                    /** @var Product $product */
                    $product = $this->productRepository->getById($productId);
                    if (!$product || !$product->isVisibleInCatalog()) {
                        $this->messageManager->addErrorMessage(__('We can\'t specify a product.'));
                        $resultRedirect->setPath('*/');

                        return $resultRedirect;
                    }
                    $productType = $product->getTypeId();
                    $requestParams['product'] = $productId;
                    switch ($productType) {
                        case 'configurable':
                            if (isset($params['super_attribute'][$productId])) {
                                $requestParams['super_attribute'] = $params['super_attribute'][$productId];
                            }
                            break;
                        case 'grouped':
                            if (isset($params['super_group'][$productId])) {
                                $requestParams['super_group'] = $params['super_group'][$productId];
                            }
                            break;
                        case 'bundle':
                            if (isset($params['bundle_option'][$productId])) {
                                $requestParams['bundle_option'] = $params['bundle_option'][$productId];
                                $requestParams['bundle_option_qty'] = $params['bundle_option_qty'][$productId];
                            }
                            break;
                        case 'downloadable':
                            if (isset($params['links'][$productId])) {
                                $requestParams['links'] = $params['links'][$productId];
                            }
                            break;
                        default:
                            break;
                    }

                    $key = 'options_' . $productId;
                    if (isset($params[$key])) {
                        $requestParams['options'] = $params['options_' . $productId];
                    }
                    $buyRequest = new DataObject($requestParams);
                    $result = $wishList->addNewItem($product, $buyRequest);
                    $productsName[] = '"' . $product->getName() . '"';
                    if (is_string($result)) {
                        throw new LocalizedException(__($result));
                    }
                }
                $wishList->save();
                $referer = $session->getBeforeWishlistUrl();
                if ($referer) {
                    $session->setBeforeWishlistUrl(null);
                } else {
                    $referer = $this->_redirect->getRefererUrl();
                }

                $this->wishListDataHelper->calculate();

                $this->messageManager->addComplexSuccessMessage(
                    'addProductSuccessMessage',
                    [
                        'product_name' => implode(', ', $productsName),
                        'referer' => $referer
                    ]
                );
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t add the item to Wish List right now: %1.', $e->getMessage())
            );
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add the item to Wish List right now.')
            );
        }
        $resultRedirect->setPath('wishlist/index/index', ['wishlist_id' => $wishList->getId()]);

        return $resultRedirect;
    }
}
