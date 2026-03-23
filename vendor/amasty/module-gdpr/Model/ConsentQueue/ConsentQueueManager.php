<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\ConsentQueue;

use Amasty\Gdpr\Model\ResourceModel\ConsentQueue;
use Magento\Framework\FlagManager;
use Magento\Framework\Stdlib\DateTime\DateTime;

class ConsentQueueManager
{
    private const FLAG_CODE = 'am_gdpr_consent_queue_generate_time';

    /**
     * @var ConsentQueue
     */
    private $consentQueue;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        ConsentQueue $consentQueue,
        FlagManager $flagManager,
        DateTime $dateTime
    ) {
        $this->consentQueue = $consentQueue;
        $this->flagManager = $flagManager;
        $this->dateTime = $dateTime;
    }

    public function getTimeLastRegenerate(): int
    {
        return (int)$this->flagManager->getFlagData(self::FLAG_CODE);
    }

    public function resetQueueItems(array $ids = []): int
    {
        return $this->consentQueue->resetFailStatus($ids);
    }

    public function regenerateQueue(): void
    {
        $this->consentQueue->clear();
        $this->consentQueue->generateQueue();
        $this->flagManager->saveFlag(self::FLAG_CODE, $this->dateTime->gmtTimestamp());
    }
}
