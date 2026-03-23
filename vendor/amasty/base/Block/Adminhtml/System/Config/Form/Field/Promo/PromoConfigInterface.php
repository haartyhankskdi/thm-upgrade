<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Block\Adminhtml\System\Config\Form\Field\Promo;

/**
 * @since 1.20.0
 */
interface PromoConfigInterface
{
    /**
     * Default configs
     */
    public const PROMO_CONFIGS = [
        self::PLAN_SUBSCRIBE => [
            self::IS_ICON_VISIBLE_KEY => true,
            self::ICON_BG_COLOR_KEY => '#ebe7ff',
            self::ICON_SRC_KEY => 'Amasty_Base::images/components/promotion-field/lock.svg',
            self::SUBSCRIBE_TEXT_KEY => 'Subscribe to Unlock',
            self::PROMO_LINK_KEY => null,
            self::COMMENT_KEY => null,
        ],
        self::PLAN_UPGRADE => [
            self::IS_ICON_VISIBLE_KEY => true,
            self::ICON_BG_COLOR_KEY => 'rgba(0, 133, 255, .1)',
            self::ICON_SRC_KEY => 'Amasty_Base::images/components/promotion-field/lock-upgrade.svg',
            self::SUBSCRIBE_TEXT_KEY => 'Upgrade Your Plan',
            self::PROMO_LINK_KEY => null,
            self::COMMENT_KEY => null,
        ]
    ];

    public const PLAN_SUBSCRIBE = 'subscribe';

    public const PLAN_UPGRADE = 'upgrade';

    public const IS_ICON_VISIBLE_KEY = 'isIconVisible';

    public const ICON_BG_COLOR_KEY = 'iconBgColor';

    public const ICON_SRC_KEY = 'iconSrc';

    public const SUBSCRIBE_TEXT_KEY = 'subscribeText';

    public const PROMO_LINK_KEY = 'promoLink';

    public const COMMENT_KEY = 'comment';
}
