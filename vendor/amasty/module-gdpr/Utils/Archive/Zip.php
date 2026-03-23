<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Utils\Archive;

use Magento\Framework\Exception\LocalizedException;

class Zip
{
    /**
     * @var \ZipArchive
     */
    private $zipArchive;

    /**
     * @throws LocalizedException
     */
    public function __construct()
    {
        $type = 'Zip';
        if (!class_exists('\ZipArchive')) {
            throw new LocalizedException(__('%1 file extension is not supported', $type));
        }
        $this->zipArchive = new \ZipArchive();
    }

    /**
     * Pack file
     *
     * @param string[] $source
     * @param string $destination
     *
     * @return string
     */
    public function pack(array $source, string $destination): string
    {
        $this->zipArchive->open($destination, \ZipArchive::CREATE);
        foreach ($source as $fileName => $fileContent) {
            $this->zipArchive->addFromString($fileName, $fileContent);
        }
        $this->zipArchive->close();

        return $destination;
    }
}
