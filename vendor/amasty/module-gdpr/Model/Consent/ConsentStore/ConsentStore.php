<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Consent\ConsentStore;

use Magento\Framework\Model\AbstractModel;

class ConsentStore extends AbstractModel
{
    public const ID = 'id';

    public const CONSENT_STORE_ID = 'store_id';

    public const CONSENT_ENTITY_ID = 'consent_entity_id';

    public const IS_ENABLED = 'is_enabled';

    public const IS_REQUIRED = 'is_required';

    public const LOG_THE_CONSENT = 'log_the_consent';

    public const HIDE_CONSENT_AFTER_USER_LEFT_THE_CONSENT = 'hide_the_consent_after_user_left_the_consent';

    public const CONSENT_LOCATION = 'consent_location';

    public const CONSENT_TEXT = 'consent_text';

    public const VISIBILITY = 'visibility';

    public const COUNTRIES = 'countries';

    public const LINK_TYPE = 'link_type';

    public const CMS_PAGE_ID = 'cms_page_id';

    public const SORT_ORDER = 'sort_order';

    public function _construct()
    {
        parent::_construct();

        $this->_init(ResourceModel\ConsentStore::class);
        $this->setIdFieldName(self::ID);
    }
}
