<?php

namespace Ebizmarts\SagePaySuite\Model\Api\ReportingSignature;

class ReportingSignatureStrategyContext
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\Api\ReportingSignature\ReportingHashingStrategyInterface[]
     */
    private $strategies;

    public function __construct(array $strategies)
    {
        $this->strategies = $strategies;
    }

    /**
     * @param string $type
     * @return \Ebizmarts\SagePaySuite\Model\Api\ReportingSignature\ReportingHashingStrategyInterface
     * @throws \InvalidArgumentException
     */
    public function getStrategy(string $type): ReportingHashingStrategyInterface
    {
        if (!isset($this->strategies[$type])) {
            throw new \InvalidArgumentException("Unknown strategy type: $type");
        }
        return $this->strategies[$type];
    }
}
