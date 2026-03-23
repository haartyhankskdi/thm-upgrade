<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Result;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use Magento\Framework\Controller\AbstractResult;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Amasty\Gdpr\Utils\Archive\ZipFactory;

class File extends AbstractResult
{
    public const FILE = 'FILE';
    public const CSV = 'csv';
    public const ZIP = 'zip';

    /**
     * @var FileDriver
     */
    private $fileDriver;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ZipFactory
     */
    private $zipFactory;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $fileExtension;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var string
     */
    private $fullFileName;

    /**
     * @var array
     */
    private $contentTypes = [
        self::CSV => 'text/csv',
        self::ZIP => 'application/zip'
    ];

    public function __construct(
        FileDriver $fileDriver,
        Filesystem $filesystem,
        ZipFactory $zipFactory,
        string $fileName = 'data',
        string $fileExtension = 'csv',
        array $data = []
    ) {
        $this->fileDriver = $fileDriver;
        $this->filesystem = $filesystem;
        $this->zipFactory = $zipFactory;
        $this->fileName = $fileName;
        $this->data = $data;
        $this->fileExtension = $fileExtension;
        $this->fullFileName = sprintf('%s.%s', $this->fileName, $this->fileExtension);
    }

    protected function render(HttpResponseInterface $response): self
    {
        $content = '';
        switch ($this->fileExtension) {
            case self::CSV:
                $content = $this->generateCsv($this->data);
                break;
            case self::ZIP:
                $files = [];
                foreach ($this->data as $fileName => $fileData) {
                    //create empty row
                    $fileData = empty($fileData) ? [[]] : $fileData;
                    $files[sprintf('%s.%s', $fileName, self::CSV)] = $this->generateCsv($fileData);
                }
                $tmpDir = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
                $this->fileDriver->createDirectory($tmpDir->getAbsolutePath(), 0755);
                $path = $tmpDir->getAbsolutePath(uniqid());
                $zip = $this->zipFactory->create()->pack($files, $path);
                $content = $this->fileDriver->fileGetContents($zip);
                $this->fileDriver->deleteFile($zip);
                break;
        }
        $this->setHeaders($response);
        $response->setContent($content);

        return $this;
    }

    protected function setHeaders(HttpResponseInterface $response)
    {
        $response
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $this->contentTypes[$this->fileExtension], true)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $this->fullFileName . '"', true)
            ->setHeader('Last-Modified', date('r'), true)
            ->setHttpResponseCode(200);
    }

    /**
     * @param array $data
     * @return string
     * @throws FileSystemException
     */
    protected function generateCsv(array $data): string
    {
        $resource = $this->fileDriver->fileOpen('php://memory', 'w');

        foreach ($data as $row) {
            $this->fileDriver->filePutCsv($resource, $row);
        }

        $fileSize = $this->fileDriver->fileTell($resource);
        $this->fileDriver->fileSeek($resource, 0);

        return $this->fileDriver->fileRead($resource, $fileSize);
    }
}
