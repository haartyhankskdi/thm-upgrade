<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class AbstractRequest extends Action
{
    public const ADMIN_RESOURCE = 'Amasty_Gdpr::requests';
}
