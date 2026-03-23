<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml;

use Magento\Backend\App\Action as BackendAction;

abstract class AbstractConsents extends BackendAction
{
    public const ADMIN_RESOURCE = 'Amasty_Gdpr::consents';
}
