<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface WithConsentSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Amasty\Gdpr\Api\Data\WithConsentInterface[]
     */
    public function getItems();

    /**
     * @param \Amasty\Gdpr\Api\Data\WithConsentInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items);
}
