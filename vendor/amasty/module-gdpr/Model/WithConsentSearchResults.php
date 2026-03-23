<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Api\Data\WithConsentSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class WithConsentSearchResults extends SearchResults implements WithConsentSearchResultsInterface
{

}
