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

namespace Mageplaza\RewardPointsUltimate\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Class Cookie
 * @package Mageplaza\RewardPointsUltimate\Helper
 */
class Cookie extends AbstractHelper
{
    const MP_REFER = 'mp_refer';

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var PublicCookieMetadata
     */
    protected $cookieMeta;

    /**
     * Cookie constructor.
     *
     * @param Context $context
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        Context $context,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;

        parent::__construct($context);
    }

    /**
     * @return null|string
     */
    public function get()
    {
        return $this->cookieManager->getCookie(self::MP_REFER);
    }

    /**
     * @param string $value
     *
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    public function set($value)
    {
        if ($this->get()) {
            $this->deleteMpRefererKeyFromCookie();
        }

        $this->cookieManager->setPublicCookie(
            self::MP_REFER,
            $value,
            $this->getCookieMetadata()
        );
    }

    /**
     * @return PublicCookieMetadata
     */
    public function getCookieMetadata()
    {
        if (!$this->cookieMeta) {
            $this->cookieMeta = $this->cookieMetadataFactory
                ->createPublicCookieMetadata()
                ->setPath('/')
                ->setDomain(null);
        }

        return $this->cookieMeta;
    }

    /**
     * @throws InputException
     * @throws FailureToSendException
     */
    public function deleteMpRefererKeyFromCookie()
    {
        if ($this->get()) {
            $this->cookieManager->deleteCookie(self::MP_REFER, $this->getCookieMetadata());
        }
    }
}
