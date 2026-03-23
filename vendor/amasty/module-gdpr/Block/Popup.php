<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Block;

use Magento\Framework\View\Element\Template;

class Popup extends Template
{
    /**
     * @var string
     */
    protected $_template = 'popup.phtml';

    /**
     * @return string
     */
    public function getTextUrl()
    {
        return $this->getUrl('gdpr/policy/policytext');
    }
}
