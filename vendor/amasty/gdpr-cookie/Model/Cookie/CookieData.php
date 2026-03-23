<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\Cookie;

use Amasty\GdprCookie\Api\CookieManagementInterface;
use Amasty\GdprCookie\Api\Data\CookieGroupsInterface;
use Amasty\GdprCookie\Model\CookieManager;
use Amasty\GdprCookie\Model\EntityVersion\CookieVersionControlService;
use Amasty\GdprCookie\Model\HtmlPreprocessor;
use Amasty\GdprCookie\Model\OptionSource\Cookie\Types;

class CookieData
{
    /**
     * @var CookieManagementInterface
     */
    private $cookieManagement;

    /**
     * @var Types
     */
    private $cookieTypes;

    /**
     * @var CookieVersionControlService
     */
    private $cookieVersionControl;

    /**
     * @var string[]
     */
    private $allowedCookieGroups;

    /**
     * @var array
     */
    private $cookieGroupDataCache = [];

    /**
     * @var HtmlPreprocessor
     */
    private $htmlPreprocessor;

    public function __construct(
        CookieManagementInterface $cookieManagement,
        CookieManager $cookieManager,
        Types $cookieTypes,
        CookieVersionControlService $cookieVersionControl,
        HtmlPreprocessor $htmlPreprocessor
    ) {
        $this->cookieManagement = $cookieManagement;
        $this->allowedCookieGroups = explode(',', $cookieManager->getAllowCookies());
        $this->cookieTypes = $cookieTypes;
        $this->cookieVersionControl = $cookieVersionControl;
        $this->htmlPreprocessor = $htmlPreprocessor;
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getGroupData(int $storeId)
    {
        if (!isset($this->cookieGroupDataCache[$storeId])) {
            $groupData = [];
            $allCookiesAllowed = in_array('0', $this->allowedCookieGroups, true);
            $groups = $this->cookieManagement->getGroups($storeId);
            $cookies = $this->cookieManagement->getCookies($storeId);

            foreach ($groups as $group) {
                $groupData[$group->getId()] = [
                    'groupId' => $group->getId(),
                    'isEssential' => $group->isEssential(),
                    'name' => $group->getName(),
                    'description' => $this->htmlPreprocessor->execute($group->getDescription()),
                    'checked' => $allCookiesAllowed || $this->isCookieGroupChecked($group),
                    'cookies' => []
                ];
            }

            if ($groupData) {
                foreach ($cookies as $cookie) {
                    if (isset($groupData[$cookie->getGroupId()])) {
                        $groupData[$cookie->getGroupId()]['cookies'][] = [
                            'name' => $cookie->getName(),
                            'description' => $this->htmlPreprocessor->execute($cookie->getDescription()),
                            'lifetime' => $cookie->getLifetime(),
                            'provider' => $cookie->getProvider(),
                            'type' => $this->cookieTypes->getCookieTypeNameById((int)$cookie->getType())
                        ];
                    }
                }
            }

            $result = [
                'groupData' => array_values($groupData),
                'lastUpdate' => $this->cookieVersionControl->getVersion($storeId)
            ];
            $this->cookieGroupDataCache[$storeId] = $result;
        }

        return $this->cookieGroupDataCache[$storeId];
    }

    public function _resetState(): void
    {
        $this->cookieGroupDataCache = [];
    }

    /**
     * @param CookieGroupsInterface $cookieGroup
     * @return bool
     */
    private function isCookieGroupChecked(CookieGroupsInterface $cookieGroup): bool
    {
        return in_array($cookieGroup->getId(), $this->allowedCookieGroups) || $cookieGroup->isEssential();
    }
}
