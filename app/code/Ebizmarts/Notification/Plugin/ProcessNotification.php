<?php

namespace Ebizmarts\Notification\Plugin;

use Ebizmarts\Notification\Model\File\Management as FilesManagement;
use Ebizmarts\Notification\Model\Notification\Management as NotificationManagement;
use Ebizmarts\Notification\Model\Module\Management as ModuleManagement;
use Magento\Backend\Model\Auth;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Psr\Log\LoggerInterface;

class ProcessNotification
{
    private const ERROR_MESSAGE = 'There was an error processing the notification.';
    private const NOTICE_MESSAGE = 'There was an issue with the directory.';

    /** @var FilesManagement $filesManagement */
    private $filesManagement;

    /** @var NotificationManagement $notificationManagement */
    private $notificationManagement;

    /** @var ModuleManagement $moduleManagement */
    private $moduleManagement;

    /** @var LoggerInterface $loggerInterface */
    private $loggerInterface;

    public function __construct(
        FilesManagement $filesManagement,
        NotificationManagement $notificationManagement,
        ModuleManagement $moduleManagement,
        LoggerInterface $loggerInterface
    ) {
        $this->filesManagement = $filesManagement;
        $this->notificationManagement = $notificationManagement;
        $this->moduleManagement = $moduleManagement;
        $this->loggerInterface = $loggerInterface;
    }

    /**
     * @param Auth $authModel
     * @return void
     */
    public function afterLogin(Auth $authModel)
    {
        try {
            $filesToProcess = $this->filesManagement->getAllFiles(
                $this->filesManagement->getMagentoPath(DirectoryList::VAR_DIR)
                . FilesManagement::NOTIFICATIONS_PATH
            );
            foreach ($filesToProcess as $file) {
                if ($this->moduleManagement->fileContainsModuleName($file)) {
                    $arrayToProcess = $this->filesManagement->getXmlArray($file);
                    foreach ($arrayToProcess as $item) {
                        $this->notificationManagement->addNotification($item);
                    }
                }
            }
        } catch (FileSystemException $fileSystemException) {
            $this->loggerInterface->notice(
                self::NOTICE_MESSAGE . $fileSystemException->getMessage()
            );
        } catch (\Exception $exception) {
            $this->loggerInterface->critical(
                self::ERROR_MESSAGE . $exception->getMessage()
            );
        }
    }
}
