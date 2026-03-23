<?php

namespace Ebizmarts\SagePaySuite\Model\Api\ReportingSignature;

class Md5HashingStrategy implements ReportingHashingStrategyInterface
{

    public function hash(string $data): string
    {
        return hash('md5', $data);
    }

    public function algorithmSignature(): string
    {
        return '';
    }
}
