<?php

namespace Ebizmarts\BrippoPayments\Helper;

use DateTime;
use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use RuntimeException;

class Logger extends AbstractHelper
{
    public const GENERAL_LOG = 'general.log';
    public const CRON_LOG = 'cron.log';
    public const SOFT_FAILED_LOG = 'soft_failed.log';
    public const SERVICE_API_LOG = 'service_api.log';
    public const INTERNAL_API_LOG = 'internal_api.log';

    /**
     * @param string $msg
     * @param string $logFilename
     * @return void
     */
    public function log($msg, string $logFilename = self::GENERAL_LOG) : void
    {
        $date = new DateTime();
        $logDir = BP . '/var/log/brippo';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $filePath = $logDir . '/' . $logFilename;
        error_log('[' . $date->format("Y-m-d H:i:s") . '] ' . $msg . "\n", 3, $filePath);
    }

    public function logOrderEvent(?OrderInterface $order, string $eventDescription): void {
        try {
            if (empty($order) || empty($order->getIncrementId())) {
                throw new LocalizedException(__('Can not log order event. Invalid order. Message was: ' . $eventDescription));
            }

            $now = new DateTime('now');
            $ym = $now->format('Y_m');
            $day = $now->format('d');
            $base = BP . "/var/log/brippo/orders/{$ym}/{$day}";

            if (!is_dir($base)) {
                if (!mkdir($base, 0775, true) && !is_dir($base)) {
                    throw new RuntimeException(sprintf(
                        'Failed to create directory "%s". Please check permissions.',
                        $base
                    ));
                }
            }

            $file = "{$base}/{$order->getIncrementId()}.log";
            $message = sprintf(
                "[%s] " . $eventDescription . "\n",
                $now->format('Y-m-d H:i:s')
            );

            if (!is_writable($base)) {
                throw new LocalizedException(
                    __('Unable to write to folder: %1', $base)
                );
            }

            file_put_contents($file, $message, FILE_APPEND);
        } catch (Exception $e) {
            $this->log($e->getMessage());
            $this->log($e->getTraceAsString());
        }
    }
}
