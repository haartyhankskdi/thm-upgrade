<?php
namespace Ebizmarts\BrippoPayments\Controller\Adminhtml\Order;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Exception;
use FilesystemIterator;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;

class Log extends Action
{
    protected $varLogDir;
    protected $logger;
    protected $jsonFactory;

    public function __construct(
        Action\Context   $context,
        JsonFactory      $jsonFactory,
        Logger           $logger
    ) {
        parent::__construct($context);
        $this->varLogDir = BP . "/var/log/brippo/orders";
        $this->logger = $logger;
        $this->jsonFactory = $jsonFactory;
    }

    public function execute()
    {
        try {
            $orderIncrementId = $this->getRequest()->getParam('orderIncrementId');
            if (empty($orderIncrementId)) {
                throw new LocalizedException(__('Order ID is required.'));
            }

            // Recursively scan for any file ending in _<orderId>.log
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->varLogDir,
                    FilesystemIterator::SKIP_DOTS)
            );
            $regex = new RegexIterator($it,
                '/' . preg_quote($orderIncrementId, '/') . '\.log$/i',
                RegexIterator::MATCH
            );

            $files = [];
            /** @var SplFileInfo $fileInfo */
            foreach ($regex as $fileInfo) {
                $files[] = $fileInfo->getPathname();
            }

            if (empty($files)) {
                throw new LocalizedException(__("No log found for order #{$orderIncrementId}"));
            }

            // pick the newest
            usort($files, function($a, $b) {
                return filemtime($b) <=> filemtime($a);
            });
            $logFile = $files[0];

            if (!file_exists($logFile) || !is_readable($logFile)) {
                throw new LocalizedException(
                    __('Unable to read log file: %1', $logFile)
                );
            }

            $contents = file_get_contents($logFile) ?: "Unable to read log";
            $response = [
                'valid' => 1,
                'data' => $contents,
            ];
        } catch (Exception $ex) {
            $response = [
                'valid' => 0,
                'message' => $ex->getMessage()
            ];
            $this->logger->log($ex->getMessage());
        }

        $resultJson = $this->jsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }
}
