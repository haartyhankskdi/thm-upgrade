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

namespace Mageplaza\FrequentlyBought\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\FrequentlyBought\Helper\Data;

/**
 * Class RemoveBlock
 *
 * @package Mageplaza\FrequentlyBought\Observer
 */
class RemoveBlock implements ObserverInterface
{
    /**
     * @var Data
     */
    private $fbtDataHelper;

    /**
     * RemoveBlock constructor.
     *
     * @param Data $fbtDataHelper
     */
    public function __construct(Data $fbtDataHelper)
    {
        $this->fbtDataHelper = $fbtDataHelper;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if ($this->fbtDataHelper->isEnabled()) {
            $layout = $observer->getLayout();
            $block = $layout->getBlock('catalog.product.related');
            if ($block && $this->fbtDataHelper->getConfigGeneral('remove_related_block')) {
                $layout->unsetElement('catalog.product.related');
            }
        }
    }
}
