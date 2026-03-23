<?php

namespace Ebizmarts\SagePaySuite\Model\Api\ReportingSignature;

class Sha256HashingStrategy implements ReportingHashingStrategyInterface
{

    public function hash(string $data): string
    {
        return hash('sha256', $data);
    }

    public function algorithmSignature(): string
    {
        return '<algorithm>sha256</algorithm>';
    }
}
