<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Utils\Reader;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\View\FileSystem;
use Magento\Framework\Filesystem\Driver\File as DriverFile;
use Psr\Log\LoggerInterface;

class File
{
    /**
     * @var FileSystem
     */
    private $viewFileSystem;

    /**
     * @var DriverFile
     */
    private $driverFile;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        FileSystem $viewFileSystem,
        DriverFile $driverFile,
        LoggerInterface $logger
    ) {
        $this->viewFileSystem = $viewFileSystem;
        $this->driverFile = $driverFile;
        $this->logger = $logger;
    }

    public function getStaticFileContent(string $fileId, array $params = []): string
    {
        try {
            $filePath = $this->viewFileSystem->getStaticFileName($fileId, $params);
            $content = $this->driverFile->fileGetContents($filePath);
        } catch (FileSystemException $e) {
            $this->logger->error($e->getMessage());
            $content = '';
        }

        return $content;
    }
}
