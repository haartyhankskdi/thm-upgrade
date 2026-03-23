<?php

namespace Ebizmarts\Notification\Model\File;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Xml\Parser;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Io\File as IoFile;

class Management
{
    public const NOTIFICATIONS_PATH = '/notifications/';
    public const XML_NODE_NAME = 'notifications';
    public const XML_NOTIFICATION_START = "<notifications";
    public const STORAGE_PATH_PERMISSIONS = 0755;

    /** @var File $file */
    private $file;

    /** @var Filesystem  $filesystem */
    private $filesystem;

    /** @var Parser $xmlParser */
    private $xmlParser;

    /** @var DirectoryList $directoryList*/
    private $directoryList;

    /** @var IoFile $ioFile */
    private $ioFile;

    /**
     * @param File $file
     * @param Filesystem $filesystem
     * @param Parser $xmlParser
     * @param DirectoryList $directoryList
     * @param IoFile $ioFile
     */
    public function __construct(
        File $file,
        Filesystem $filesystem,
        Parser $xmlParser,
        DirectoryList $directoryList,
        IoFile $ioFile
    ) {
        $this->file = $file;
        $this->filesystem = $filesystem;
        $this->xmlParser = $xmlParser;
        $this->directoryList = $directoryList;
        $this->ioFile = $ioFile;
    }

    /**
     * @param string $module
     * @param string $content
     * @return void
     * @throws FileSystemException
     */
    public function saveXmlFile($module, $content)
    {
        if (!$this->directoryExists(DirectoryList::VAR_DIR)) {
            $this->createDirectory();
        }

        $fileName = $this->getMagentoPath(DirectoryList::VAR_DIR)
            . self::NOTIFICATIONS_PATH
            . $module;
        $this->removeFile($fileName);
        $this->writeXmlFile($fileName, $content);
    }

    /**
     * @param string $file
     * @return []
     */
    public function getXmlArray($file)
    {
        $resultArray = $this->xmlParser->load($file)->xmlToArray()[self::XML_NODE_NAME];
        return !empty($resultArray) ? $resultArray : [];
    }

    /**
     * @param string $directoryPath
     * @return string[]
     * @throws FileSystemException
     */
    public function getAllFiles($directoryPath)
    {
        $filesToProcess = [];
        if ($this->directoryExists(DirectoryList::VAR_DIR)) {
            $filesToProcess = $this->file->readDirectory($directoryPath);
        }
        return $filesToProcess;
    }

    /**
     * @param string $directoryCode
     * @return string
     * @throws FileSystemException
     */
    public function getMagentoPath($directoryCode)
    {
        return $this->directoryList->getPath($directoryCode);
    }

    /**
     * @param $directoryCode
     * @return bool
     * @throws FileSystemException
     */
    private function directoryExists($directoryCode)
    {
        $magentoPath = $this->getMagentoWritePath($directoryCode);
        return $magentoPath->isExist(self::NOTIFICATIONS_PATH);
    }

    /**
     * @param string $pathFile
     * @return void
     * @throws FileSystemException
     */
    private function removeFile($pathFile)
    {
        if ($this->fileExists($pathFile)) {
            $this->file->deleteFile($pathFile);
        }
    }

    /**
     * @param string $pathFile
     * @return bool
     * @throws FileSystemException
     */
    private function fileExists($pathFile)
    {
        return $this->file->isExists($pathFile);
    }

    /**
     * @param string|null $directory
     * @throws LocalizedException
     */
    private function createDirectory()
    {
        $magentoPath = $this->getMagentoWritePath(DirectoryList::VAR_DIR);
        $magentoPath->create(self::NOTIFICATIONS_PATH);
        $magentoPath->changePermissions(self::NOTIFICATIONS_PATH, self::STORAGE_PATH_PERMISSIONS);
    }

    /**
     * @param string $directoryCode
     * @return WriteInterface
     * @throws FileSystemException
     */
    private function getMagentoWritePath($directoryCode)
    {
        return $this->filesystem->getDirectoryWrite($directoryCode);
    }

    /**
     * @param string $fileName
     * @param string $content
     * @return void
     */
    private function writeXmlFile($fileName, $content)
    {
        $content = $this->removeUnnecessaryContent($content);
        $this->ioFile->write($fileName, $content);
    }

    /**
     * @param string $content
     * @return string
     */
    private function removeUnnecessaryContent($content)
    {
        $startPosition = strpos($content, self::XML_NOTIFICATION_START);
        if ($startPosition !== false) {
            $content = substr_replace($content, "", 0, $startPosition);
        }

        return $content;
    }
}
