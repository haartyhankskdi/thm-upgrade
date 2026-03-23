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

namespace Mageplaza\RewardPointsUltimate\Plugin\Order\Pdf\Items\Creditmemo;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Sales\Model\Order\Pdf\Items\AbstractItems;
use Mageplaza\RewardPoints\Helper\Point;

/**
 * Class DefaultCreditmemo
 * @package Mageplaza\RewardPointsUltimate\Plugin\Order\Pdf\Items\Creditmemo
 */
class DefaultCreditmemo
{
    /**
     * @var StringUtils
     */
    protected $string;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var Point
     */
    protected $pointHelper;

    /**
     * DefaultCreditmemo constructor.
     *
     * @param StringUtils $string
     * @param FilterManager $filterManager
     * @param Point $pointHelper
     */
    public function __construct(
        StringUtils $string,
        FilterManager $filterManager,
        Point $pointHelper
    ) {
        $this->string = $string;
        $this->filterManager = $filterManager;
        $this->pointHelper = $pointHelper;
    }

    /**
     * @param AbstractItems $subject
     * @param callable $proceed
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function aroundDraw(AbstractItems $subject, callable $proceed)
    {
        $item = $subject->getItem();
        $mpRewardSellPoints = $item->getMpRewardSellPoints();
        if (!$mpRewardSellPoints) {
            return $proceed();
        }

        $order = $subject->getOrder();
        $pdf = $subject->getPdf();
        $page = $subject->getPage();
        $lines = [];

        // draw Product name
        $lines[0] = [['text' => $this->string->split($item->getName(), 35, true, true), 'feed' => 35]];

        // draw SKU
        $lines[0][] = [
            'text' => $this->string->split($subject->getSku($item), 17),
            'feed' => 255,
            'align' => 'right',
        ];

        $sellPointFormat = $this->pointHelper->format(($mpRewardSellPoints * $item->getQty()), false);
        // draw Total (ex)
        $lines[0][] = [
            'text' => $sellPointFormat,
            'feed' => 330,
            'font' => 'bold',
            'align' => 'right',
        ];

        // draw Discount
        $lines[0][] = [
            'text' => $order->formatPriceTxt(-$item->getDiscountAmount()),
            'feed' => 380,
            'font' => 'bold',
            'align' => 'right',
        ];

        // draw QTY
        $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 445, 'font' => 'bold', 'align' => 'right'];

        // draw Tax
        $lines[0][] = [
            'text' => $order->formatPriceTxt($item->getTaxAmount()),
            'feed' => 495,
            'font' => 'bold',
            'align' => 'right',
        ];

        // draw Total (inc)
        $lines[0][] = [
            'text' => $sellPointFormat,
            'feed' => 565,
            'font' => 'bold',
            'align' => 'right',
        ];

        // draw options
        $options = $subject->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = [
                    'text' => $this->string->split($this->filterManager->stripTags($option['label']), 40, true, true),
                    'font' => 'italic',
                    'feed' => 35,
                ];

                // draw options value
                $printValue = isset($option['print_value'])
                    ? $option['print_value']
                    : $this->filterManager->stripTags($option['value']);
                $lines[][] = ['text' => $this->string->split($printValue, 30, true, true), 'feed' => 40];
            }
        }

        $lineBlock = ['lines' => $lines, 'height' => 20];

        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $subject->setPage($page);
    }
}
