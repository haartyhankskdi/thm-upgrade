<?php

namespace Ebizmarts\Notification\Cron;

use Ebizmarts\Notification\Model\Module\Management as ModulesManagement;
use Ebizmarts\Notification\Model\File\Management as FileManager;
use Ebizmarts\Notification\Model\Notification\Management as NotificationManagement;
use Magento\Framework\HTTP\Adapter\Curl as CurlAdapter;
use Psr\Log\LoggerInterface;

class Notification
{
    private const NOTIFICATION_URL = 'https://ebizmarts-website.s3.amazonaws.com/notifications/';
    private const METHOD_GET = 'GET';
    private const VERSION_11 = '1.1';

    /** @var FileManager $fileManager */
    private $fileManager;

    /** @var ModulesManagement $modulesManagement */
    private $modulesManagement;

    /** @var LoggerInterface $loggerInterface */
    private $loggerInterface;

    /** @var CurlAdapter $curl */
    private $curl;

    /**
     * Notification constructor
     *
     * @param FileManager $fileManager
     * @param ModulesManagement $modulesManagement
     * @param LoggerInterface $loggerInterface
     * @param CurlAdapter $curl
     */
    public function __construct(
        FileManager $fileManager,
        ModulesManagement $modulesManagement,
        LoggerInterface $loggerInterface,
        CurlAdapter $curl
    ) {
        $this->fileManager = $fileManager;
        $this->modulesManagement = $modulesManagement;
        $this->loggerInterface = $loggerInterface;
        $this->curl = $curl;
    }

    public function process()
    {
        $ebizmartsModules = $this->modulesManagement->getEbizmartsModules();
        $this->getXmlNotificationFiles($ebizmartsModules);
    }

    /**
     * @param $ebizmartsModules
     * @return void
     */
    private function getXmlNotificationFiles($ebizmartsModules)
    {
        try {
            foreach ($ebizmartsModules as $ebizmartsModule) {
                // Make a cURL request to the external URL
                $response = $this->getContentFile(self::NOTIFICATION_URL . $ebizmartsModule);
                //save file
                $this->fileManager->saveXmlFile($ebizmartsModule, $response);
            }
        } catch (\Exception $exception) {
            $this->loggerInterface->critical(
                NotificationManagement::ERROR_MESSAGE . $exception->getMessage()
            );
            $this->loggerInterface->critical($exception);
        }
    }

    /**
     * @param string $notificationUrl
     * @return string
     */
    private function getContentFile($notificationUrl)
    {
        $this->initializeCurl();
        // @codingStandardsIgnoreStart
        $this->curl->write(
            self::METHOD_GET,
            $notificationUrl,
            self::VERSION_11,
            ['Content-type: application/json']
        );
        // @codingStandardsIgnoreEnd
        return $this->curl->read();
    }

    /**
     * @return void
     */
    private function initializeCurl()
    {
        $config = [
            'timeout'    => 120,
            'verifyhost' => 2,
        ];

        $this->curl->setConfig($config);
    }
}
