<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Block;

use Amasty\GdprCookie\Model\CookiePolicy;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Template\Context;

class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var CookiePolicy
     */
    private $cookiePolicy;

    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        CookiePolicy $cookiePolicy,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->cookiePolicy = $cookiePolicy;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if (!$this->cookiePolicy->isCookiePolicyAllowed()) {
            return '';
        }

        return parent::toHtml();
    }

    /**
     * @return bool
     */
    public function isCurrent()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return '*/*/*';
    }

    public function getAttributes()
    {
        return ['data-amcookie-js' => 'footer-link'];
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return __('Cookie Settings');
    }
}
