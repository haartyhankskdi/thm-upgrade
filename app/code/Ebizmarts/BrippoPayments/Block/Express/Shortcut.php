<?php

namespace Ebizmarts\BrippoPayments\Block\Express;

use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\Express as ExpressHelper;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Model\Config\Source\ExpressLocation;
use Ebizmarts\BrippoPayments\Model\Express;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\CheckoutAgreements\Model\Agreement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class Shortcut extends Button implements ShortcutInterface
{
    const ALIAS_ELEMENT_INDEX = 'alias';

    /** @var string */
    protected $_template = 'Ebizmarts_BrippoPayments::minicart/express.phtml';

    /** @var bool */
    private $isMiniCart = false;

    protected $checkoutSession;

    public function __construct(
        Template\Context $context,
        DataHelper $dataHelper,
        Express $expressPaymentMethod,
        Logger $logger,
        Registry $registry,
        ExpressHelper $expressHelper,
        CheckoutSession $checkoutSession,
        Agreement $checkoutAgreement,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $dataHelper,
            $expressPaymentMethod,
            $logger,
            $registry,
            $expressHelper,
            $checkoutAgreement,
            $data
        );
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return array|mixed|string|null
     */
    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    /**
     * @param bool $isCatalog
     * @return $this
     */
    public function setIsInCatalogProduct($isCatalog)
    {
        $this->isMiniCart = !$isCatalog;

        return $this;
    }

    public function setIsShoppingCart($isShoppingCart)
    {
        $this->isShoppingCart = $isShoppingCart;
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function shouldRender(): bool
    {
        // avoids showing the button twice on the cart page on the totals section
        if ($this->getIsCart()) {
            return false;
        }

        if ($this->getIsInCatalogProduct()) {
            return false;
        }

        $isAvailableAtLocation = $this->expressHelper->isAvailableAtLocation(
            $this->checkoutSession->getQuote()->getStoreId(),
            ExpressLocation::MINICART
        );

        return $isAvailableAtLocation && $this->isMiniCart;
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _toHtml()
    {
        if (!$this->shouldRender()) {
            return '';
        }
        return parent::_toHtml();
    }
}
