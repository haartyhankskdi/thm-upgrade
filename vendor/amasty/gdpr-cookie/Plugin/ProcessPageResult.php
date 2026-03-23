<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Plugin;

use Amasty\GdprCookie\Api\CookieManagementInterface;
use Amasty\GdprCookie\Model\ConfigProvider;
use Amasty\GdprCookie\Model\CookieManager;
use Amasty\GdprCookie\Model\CookiePolicy;
use Amasty\GdprCookie\Model\GoogleAnalytics\IsGaCookieAllowed;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\StoreManagerInterface;

class ProcessPageResult
{
    public const GOOGLE_TAG_MANAGER_REG = '/\'https:\/\/www\.googletagmanager\.com\/gtm\.js\?id=.*?;/is';

    /**
     * @var IsGaCookieAllowed
     */
    private $isGaCookieAllowed;

    public function __construct(
        ?ConfigProvider $configProvider, //@deprecated backward compatibility
        CookieManager $cookieManager, //@deprecated backward compatibility
        CookieManagementInterface $cookieManagement, //@deprecated backward compatibility
        StoreManagerInterface $storeManager, //@deprecated backward compatibility
        CookiePolicy $cookiePolicy, //@deprecated backward compatibility
        ?IsGaCookieAllowed $isGaCookieAllowed = null //todo: move to not optional
    ) {
        $this->isGaCookieAllowed =  $isGaCookieAllowed ?: ObjectManager::getInstance()->get(IsGaCookieAllowed::class);
    }

    public function afterRenderResult(
        ResultInterface $subject,
        ResultInterface $result,
        ResponseInterface $response
    ): ResultInterface {
        if (!$this->isGaCookieAllowed->execute()) {
            $output = $response->getBody();
            if (preg_match(self::GOOGLE_TAG_MANAGER_REG, $output, $match)) {
                $output = preg_replace(self::GOOGLE_TAG_MANAGER_REG, "'';", $output);
                $response->setBody($output);
            }
        }

        return $result;
    }
}
