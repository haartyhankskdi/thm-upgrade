<?php

namespace Ebizmarts\BrippoPayments\Helper;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Magento\Framework\Module\Dir as ModuleDir;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\Domains as BrippoDomainsApi;
use Magento\Store\Model\ScopeInterface;

class ApplePay extends AbstractHelper
{
    const DOMAIN_ASSOCIATION_FILE_NAME = 'apple-developer-merchantid-domain-association';
    const DOMAIN_ASSOCIATION_FOLDER_PATH = '.well-known/';
    const STRIPE_DOMAIN_ASSOCIATION_FILE_URL =
        'https://stripe.com/files/apple-pay/apple-developer-merchantid-domain-association';

    /** @var DirectoryList */
    protected $directoryList;

    /** @var FileIo */
    protected $fileIo;

    /** @var ModuleDir */
    protected $moduleDir;

    /** @var BrippoDomainsApi */
    protected $brippoApiDomains;

    /** @var DataHelper */
    protected $dataHelper;

    /** @var Logger */
    protected $logger;

    /**
     * @param Context $context
     * @param Logger $logger
     * @param DataHelper $dataHelper
     * @param DirectoryList $directoryList
     * @param FileIo $fileIo
     * @param ModuleDir $moduleDir
     * @param BrippoDomainsApi $brippoApiDomains
     */
    public function __construct(
        Context             $context,
        Logger              $logger,
        DataHelper          $dataHelper,
        DirectoryList       $directoryList,
        FileIo              $fileIo,
        ModuleDir           $moduleDir,
        BrippoDomainsApi    $brippoApiDomains
    ) {
        parent::__construct($context);
        $this->directoryList = $directoryList;
        $this->fileIo = $fileIo;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->moduleDir = $moduleDir;
        $this->brippoApiDomains = $brippoApiDomains;
    }

    /**
     * @param $storeDomain
     * @param int $scopeId
     * @param string $scopeType
     * @return array
     * @throws LocalizedException
     */
    public function registerStoreDomain(
        $storeDomain = null,
        int  $scopeId = 0,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): array {
        if (empty($storeDomain)) {
            $storeDomain = $this->dataHelper->getStoreDomain();
        }

        return $this->brippoApiDomains->registerAndValidate(
            $this->dataHelper->isLiveMode($scopeId, $scopeType),
            $storeDomain
        );
    }

    /**
     * @return bool
     * @throws FileSystemException
     */
    public function isDomainAssociationFileInPlace():bool
    {
        $folderDestination = $this->directoryList->getPath('pub')
            . '/'
            . self::DOMAIN_ASSOCIATION_FOLDER_PATH;
        $fileName = $folderDestination . self::DOMAIN_ASSOCIATION_FILE_NAME;
        return $this->fileIo->fileExists($fileName);
    }

    /**
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function placeDomainAssociationFile():void
    {
        $folderName = $this->directoryList->getPath('pub') . '/' . self::DOMAIN_ASSOCIATION_FOLDER_PATH;
        $this->fileIo->checkAndCreateFolder($folderName);

        $moduleEtcFolder = $this->moduleDir->getDir(
            'Ebizmarts_BrippoPayments',
            ModuleDir::MODULE_ETC_DIR
        );
        $fileSource = $moduleEtcFolder . '/'. self::DOMAIN_ASSOCIATION_FILE_NAME;
        $folderDestination = $this->directoryList->getPath('pub')
            . '/'
            . self::DOMAIN_ASSOCIATION_FOLDER_PATH;
        $fileDestination = $folderDestination . self::DOMAIN_ASSOCIATION_FILE_NAME;

        if ($this->getOnlineCertificate($fileDestination)) {
            return;
        }

        $copyResponse = $this->fileIo->cp($fileSource, $fileDestination);
        if (!$copyResponse) {
            throw new FileSystemException(
                __("Unable to place Apple's Domain-Association-File. Please manually move "
                    . $fileSource . " to " . $folderDestination
                    . ". Create directory if necessary and make sure is publicly accessible at "
                    . $this->dataHelper->getStoreUrl() . self::DOMAIN_ASSOCIATION_FOLDER_PATH
                    . self::DOMAIN_ASSOCIATION_FILE_NAME)
            );
        }
    }

    private function getOnlineCertificate($fileDestination):bool
    {
        try {
            $fileContent = file_get_contents(self::STRIPE_DOMAIN_ASSOCIATION_FILE_URL);
            file_put_contents($fileDestination, $fileContent);
            return true;
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            return false;
        }
    }
}
