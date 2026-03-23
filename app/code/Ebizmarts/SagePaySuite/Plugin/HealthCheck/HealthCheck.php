<?php

namespace Ebizmarts\SagePaySuite\Plugin\HealthCheck;

use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Model\Config;

class HealthCheck
{
    public const ERROR_MESSAGE = "ebizmarts Payment Suite 10 license activation is invalid";

    /** @var Config $config */
    private $config;

    /** @var Data $config */
    private $suiteHelper;

    public function __construct(
        Data $suiteHelper,
        Config $config
    ) {
        $this->config   = $config;
        $this->suiteHelper   = $suiteHelper;
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return ($this->suiteHelper->verify() || $this->config->getMode() != Config::MODE_LIVE);
    }
}
