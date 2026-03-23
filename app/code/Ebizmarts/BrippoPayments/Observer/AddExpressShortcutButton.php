<?php

namespace Ebizmarts\BrippoPayments\Observer;

use Magento\Catalog\Block\ShortcutButtons;
use Magento\Checkout\Block\QuoteShortcutButtons;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Element\Template;
use Ebizmarts\BrippoPayments\Block\Express\Shortcut as PaymentRequestButtonShortcut;

class AddExpressShortcutButton implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        /** @var ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();

        //PAYMENT REQUEST BUTTON
        /** @var Template $shortcut */
        $shortcut = $shortcutButtons->getLayout()->createBlock(PaymentRequestButtonShortcut::class, '', []);
        $shortcut->setIsInCatalogProduct(
            $observer->getEvent()->getIsCatalogProduct()
        )->setShowOrPosition(
            $observer->getEvent()->getOrPosition()
        );
        $shortcut->setIsShoppingCart($observer->getEvent()->getIsShoppingCart());
        $shortcut->setIsCart(get_class($shortcutButtons) == QuoteShortcutButtons::class);
        $shortcutButtons->addShortcut($shortcut);
    }
}
