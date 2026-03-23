<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Hyva\Widgets\Block\Widgets;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Content extends Template implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = "widget/content.phtml";
}
