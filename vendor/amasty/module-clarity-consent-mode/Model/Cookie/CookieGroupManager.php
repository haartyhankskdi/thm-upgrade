<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Cookie Clarity Consent Mode
 */

namespace Amasty\ClarityConsentMode\Model\Cookie;

use Amasty\GdprCookie\Api\Data\CookieGroupsInterface;
use Amasty\GdprCookie\Model\CookieGroupFactory;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroup as CookieGroupResource;

class CookieGroupManager
{
    public const CLARITY_COOKIE_GROUP_NAME = 'Clarity';
    public const CLARITY_COOKIE_DEFAULT_GROUP = '0';

    public function __construct(
        private readonly CookieGroupFactory $cookieGroupFactory,
        private readonly CookieGroupResource $cookieGroupResource
    ) {
    }

    public function getClarityGroupId(): ?string
    {
        /** @var \Amasty\GdprCookie\Model\CookieGroup $group */
        $group = $this->cookieGroupFactory->create();
        $this->cookieGroupResource->load($group, self::CLARITY_COOKIE_GROUP_NAME, CookieGroupsInterface::NAME);

        return $group->getId() ?? self::CLARITY_COOKIE_DEFAULT_GROUP;
    }
}
