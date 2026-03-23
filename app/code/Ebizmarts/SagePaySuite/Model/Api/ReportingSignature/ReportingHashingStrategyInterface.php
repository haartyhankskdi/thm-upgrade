<?php

declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Model\Api\ReportingSignature;

interface ReportingHashingStrategyInterface
{
    /**
     * Compute a hash of the given message.
     *
     * @param string $data
     * @return string
     */
    public function hash(string $data): string;

    public function algorithmSignature(): string;
}
