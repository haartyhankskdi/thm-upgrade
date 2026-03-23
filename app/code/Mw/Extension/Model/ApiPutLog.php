<?php

namespace Mw\Extension\Model;

use Mw\Extension\Api\PutLogInterface;
// use Zend\Log\Writer\Stream;
// use Zend\Log\Logger;

// use Magento\Framework\Filesystem;
// use Magento\Framework\App\Filesystem\DirectoryList;
// use Magento\Framework\Exception\FileSystemException;
// use Magento\Framework\Exception\LocalizedException;
// use Magento\Framework\Filesystem\Directory\WriteInterface;

class ApiPutLog implements PutLogInterface
{
    // /**
    //  * @var Filesystem
    //  */
    // protected $file;

    // /**
    //  * @var WriteInterface
    //  */
    // protected $newDirectory;

    protected $logger;
    public function __construct(
        \Magento\Framework\Webapi\Rest\Request $request
        // Filesystem $file
        // \Psr\Log\LoggerInterface $logger
    ) {
        $this->request = $request;
        // $this->logger = $logger;
        // $this->newDirectory = $file->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    public function putLog()
    {
        $data = $this->request->getBodyParams();
        $response = [
            'status' => 0,
            'message' => 'Error while logging data.'
        ];
        if (count($data)) {
            try {
                // $logPath = "log/app";
                // $newDirectory = $this->newDirectory->create($logPath);
                $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/app/' . $data['name'] . '.log');
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $logger->info('---------------------------------------------------------------------');
                $logger->info(print_r($data, true));
                $response = [
                    'status' => 1,
                    'message' => 'success'
                ];
            } catch (\Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => $e->getMessage()
                ];
            }
        }
        return json_encode($response);
        exit();
    }
}
