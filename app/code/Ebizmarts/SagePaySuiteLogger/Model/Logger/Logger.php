<?php

namespace Ebizmarts\SagePaySuiteLogger\Model\Logger;

use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Model\Config;

class Logger extends \Monolog\Logger
{
    /**
     * SagePaySuite log files
     */
    public const LOG_REQUEST     = 150;
    public const LOG_CRON        = 250;
    public const LOG_EXCEPTION   = 350;
    public const LOG_DEBUG       = 450;
    public const LOG_ACS_URL     = 520;
    public const LOG_ORDER_STATE = 650;
    public const LOG_CRON_TRY_CREATE_INVOICE = 750;
    public const LOG_CRON_SYNC_FROM_API = 850;
    public const LOG_REFUND = 850;

    // @codingStandardsIgnoreStart
    protected static $levels = [
        self::LOG_REQUEST     => 'Request',
        self::LOG_CRON        => 'Cron',
        self::LOG_EXCEPTION   => 'Exception',
        self::LOG_DEBUG       => 'Debug',
        self::LOG_ACS_URL     => 'AcsUrl',
        self::LOG_ORDER_STATE => 'OrderState',
        self::LOG_CRON_TRY_CREATE_INVOICE => 'CronTryCreateInvoice',
        self::LOG_CRON_SYNC_FROM_API => 'CronSyncFromApi',
        self::LOG_REFUND => 'Refund'
    ];
    // @codingStandardsIgnoreEnd

    /** @var Config */
    private $config;

    /** @var Data */
    private $suiteHelper;

    /**
     * Logger constructor.
     * @param Config $config
     * @param Data $suiteHelper
     * @param string $name
     * @param array $handlers
     */
    public function __construct(
        Config $config,
        Data $suiteHelper,
        string $name,
        array $handlers
    ) {
        parent::__construct($name, $handlers);
        $this->config      = $config;
        $this->suiteHelper = $suiteHelper;
    }

    /**
     * @param $logType
     * @param $message
     * @param array $context
     * @return bool
     */
    public function sageLog($logType, $message, $context = [])
    {
        $message = $this->messageForLog($message);
        $message .= "\r\n";

        return $this->addRecord($logType, $message, $context);
    }

    public function logException($exception, $context = [])
    {
        $message = $exception->getMessage();
        $message .= "\n";
        $message .= $exception->getTraceAsString();
        $message .= "\r\n\r\n";

        return $this->addRecord(self::LOG_EXCEPTION, $message, $context);
    }

    /**
     * @param string|array $message
     * @param array $context
     * @return bool
     */
    public function debugLog($message, $context = [])
    {
        $recordSaved = false;
        if ($this->config->getDebugMode()) {
            $recordSaved = $this->sageLog(self::LOG_DEBUG, $message, $context);
        }
        return $recordSaved;
    }

    /**
     * @param  string|array|object $message
     * @param array $context
     * @return bool
     */
    public function ascUrlLog($message, $context = [])
    {
        $recordSaved = false;
        if (!$this->config->getDebugMode() || $message === null) {
            return $recordSaved;
        }
        if (is_object($message)) {
            $message = (array)$message;
        }
        if (isset($message['acsUrl'])) {
            $message = $this->messageForLog($message['acsUrl']);
            $message .= "\r\n";
            $recordSaved = $this->addRecord(self::LOG_ACS_URL, $message, $context);
        }
        return $recordSaved;
    }

    /**
     * @param $message
     * @return string
     */
    private function messageForLog($message)
    {
        if ($message === null) {
            $message = "NULL";
        }

        if (is_array($message) || is_object($message)) {
            if (is_array($message)) {
                $message = $this->suiteHelper->removePersonalInformation($message);
            } else {
                $message = $this->suiteHelper->removePersonalInformationObject($message);
            }
            $message = json_encode($message, JSON_PRETTY_PRINT);

            if (!empty(json_last_error())) {
                $message .= "\r\n";
                $message .= json_last_error_msg();
            }
        }

        $message = (string)$message;

        return $message;
    }

    /**
     * @param string $paymentMethod
     * @param string $incrementId
     * @param int $cartId
     */
    public function orderStartLog($paymentMethod, $incrementId, $cartId)
    {
        $message = "\n";
        $message .= '---------- ';
        $message .= "Starting order with " . $paymentMethod . ": Order: " . $incrementId . " - Cart: " . $cartId;
        $message .= ' ----------';
        $this->sageLog(self::LOG_REQUEST, $message);
        $this->debugLog($message);
    }

    /**
     * @param $vpstxid
     * @param $incrementId
     * @param $cartId
     */
    public function orderEndLog($incrementId, $cartId, $vpstxid = null)
    {
        $message = "\n";
        $message .= '---------- ';
        $message .= "End of Order " . $incrementId;
        $message .= " - Cart: " . $cartId;
        if (!empty($vpstxid)) {
            $message .= " - VPSTxId: " . $vpstxid;
        }
        $message .= ' ----------';
        $this->sageLog(self::LOG_REQUEST, $message);
        $this->debugLog($message);
    }
}
