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

namespace Mageplaza\FrequentlyBought\Controller\Cart;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\FrequentlyBought\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class Add
 *
 * @package Mageplaza\FrequentlyBought\Controller\Cart
 */
class Add extends \Magento\Checkout\Controller\Cart\Add
{
    /**
     * @var Data
     */
    protected $fbtDataHelper;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CartHelper
     */
    protected $cartHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param Data $fbtDataHelper
     * @param LoggerInterface $logger
     * @param Escaper $escaper
     * @param CartHelper $cartHelper
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        Cart $cart,
        ProductRepositoryInterface $productRepository,
        Data $fbtDataHelper,
        LoggerInterface $logger,
        Escaper $escaper,
        CartHelper $cartHelper
    ) {
        $this->fbtDataHelper = $fbtDataHelper;
        $this->escaper = $escaper;
        $this->logger = $logger;
        $this->cartHelper = $cartHelper;

        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $productRepository
        );
    }

    /**
     * Add product to shopping cart action
     *
     * @return Redirect|Add
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        if (!$this->fbtDataHelper->isEnabled()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $storeId = $this->_storeManager->getStore()->getId();
        $params = $this->getRequest()->getParams();
        try {
            if (!empty($params['mageplaza_fbt'])) {
                $productsName = [];
                foreach ($params['mageplaza_fbt'] as $productId => $value) {
                    if (!$value) {
                        continue;
                    }
                    $paramsFbt = [];
                    /** @var Product $product */
                    $product = $this->productRepository->getById($productId, false, $storeId);
                    $productType = $product->getTypeId();
                    $paramsFbt['product'] = $productId;
                    switch ($productType) {
                        case 'configurable':
                            if (isset($params['super_attribute'][$productId])) {
                                $paramsFbt['super_attribute'] = $params['super_attribute'][$productId];
                            }
                            break;
                        case 'grouped':
                            if (isset($params['super_group'][$productId])) {
                                $paramsFbt['super_group'] = $params['super_group'][$productId];
                            }
                            break;
                        case 'bundle':
                            if (isset($params['bundle_option'][$productId])) {
                                $paramsFbt['bundle_option'] = $params['bundle_option'][$productId];
                                $paramsFbt['bundle_option_qty'] = $params['bundle_option_qty'][$productId] ?? null;
                            }
                            break;
                        case 'downloadable':
                            if (isset($params['links'][$productId])) {
                                $paramsFbt['links'] = $params['links'][$productId];
                            }
                            break;
                        default:
                            break;
                    }
                    $key = 'options_' . $productId;
                    if (isset($params[$key])) {
                        $paramsFbt['options'] = $params['options_' . $productId];
                    }
                    $productsName[] = '"' . $product->getName() . '"';
                    $this->cart->addProduct($product, $paramsFbt);
                }
                $this->cart->save();
                if ($this->usePopup() && !$this->cart->getQuote()->getHasError()) {
                    $message = __('You added %1 to your shopping cart.', implode(', ', $productsName));

                    return $this->getResponse()->representJson(Data::jsonEncode([
                        'success' => true,
                        'message' => $message
                    ]));
                }
                if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                    if (!$this->cart->getQuote()->getHasError()) {
                        $message = __('You added %1 to your shopping cart.', implode(', ', $productsName));
                        $this->messageManager->addSuccessMessage($message);
                    }

                    return $this->goBack();
                }
            } else {
                if ($this->usePopup()) {
                    $message = __('Please select product(s).');

                    return $this->getResponse()->representJson(Data::jsonEncode([
                        'error' => true,
                        'message' => [$message]
                    ]));
                }
                $this->messageManager->addError(
                    $this->escaper->escapeHtml(__('Please select product(s).'))
                );
            }
        } catch (LocalizedException $e) {
            if ($this->usePopup()) {
                if ($this->_checkoutSession->getUseNotice(true)) {
                    $messages = [$e->getMessage()];
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                }

                return $this->getResponse()->representJson(Data::jsonEncode(['error' => true, 'message' => $messages]));
            }
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNotice(
                    $this->escaper->escapeHtml($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addError(
                        $this->escaper->escapeHtml($message)
                    );
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);

            if (!$url) {
                $cartUrl = $this->cartHelper->getCartUrl();
                $url = $this->_redirect->getRedirectUrl($cartUrl);
            }

            return $this->goBack($url);
        } catch (Exception $e) {
            $message = __('We can\'t add this item to your shopping cart right now.');
            if ($this->usePopup()) {
                return $this->getResponse()->representJson(Data::jsonEncode([
                    'error' => true,
                    'message' => [$message]
                ]));
            }
            $this->messageManager->addException($e, $message);
            $this->logger->critical($e);

            return $this->goBack();
        }
    }

    /**
     * @return bool
     */
    public function usePopup()
    {
        return $this->fbtDataHelper->usePopup() && $this->getRequest()->isAjax();
    }
}
