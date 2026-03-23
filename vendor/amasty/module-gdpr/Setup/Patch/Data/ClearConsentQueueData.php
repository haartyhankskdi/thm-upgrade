<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Setup\Patch\Data;

use Amasty\Gdpr\Api\Data\ConsentQueueInterface;
use Amasty\Gdpr\Model\ResourceModel\ConsentQueue;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ClearConsentQueueData implements DataPatchInterface
{
    /**
     * @var ConsentQueue
     */
    private $consentQueue;

    public function __construct(
        ConsentQueue $consentQueue
    ) {
        $this->consentQueue = $consentQueue;
    }

    public function apply()
    {
        // Workaround for error: "DDL statements are not allowed in transactions"
        $this->consentQueue->getConnection()
            ->delete($this->consentQueue->getMainTable(), ConsentQueueInterface::ID . ' > 0');

        return $this;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
