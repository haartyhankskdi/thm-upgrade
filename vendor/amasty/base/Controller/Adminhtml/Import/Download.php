<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Controller\Adminhtml\Import;

use Amasty\Base\Model\Response\File\FileOctetResponse;
use Amasty\Base\Model\Response\OctetResponseInterfaceFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Module\Dir\Reader;

class Download
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var OctetResponseInterfaceFactory
     */
    private OctetResponseInterfaceFactory $responseFactory;

    public function __construct(
        Reader $reader,
        ReadFactory $readFactory,
        FileFactory $fileFactory,
        RequestInterface $request,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        ?OctetResponseInterfaceFactory $responseFactory = null // TODO move to not optional
    ) {
        $this->reader = $reader;
        $this->readFactory = $readFactory;
        $this->fileFactory = $fileFactory;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        // OM form backward compatibility
        $this->responseFactory = $responseFactory ?? ObjectManager::getInstance()
            ->get(OctetResponseInterfaceFactory::class);
    }

    /**
     * @param string $moduleName
     * @return Redirect|HttpInterface
     */
    public function downloadSample($moduleName)
    {
        if (empty($moduleName)) {
            return $this->emptyModuleName();
        }

        if (!preg_match('/[a-z0-9\-]+/i', $this->request->getParam('filename'))) {
            return $this->noEntityFound();
        }

        $fileName = $this->request->getParam('filename') . '.csv';
        $moduleDir = $this->reader->getModuleDir('', $moduleName);
        $fileAbsolutePath = $moduleDir . '/Files/Sample/' . $fileName;
        $directoryRead = $this->readFactory->create($moduleDir);
        $filePath = $directoryRead->getRelativePath($fileAbsolutePath);

        if (!$directoryRead->isFile($filePath)) {
            return $this->noEntityFound();
        }

        $this->fileFactory->create(
            $fileName,
            null,
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
            'application/octet-stream',
            $directoryRead->stat($filePath)['size']
        );

        /** @var FileOctetResponse $response */
        $response = $this->responseFactory->create($fileAbsolutePath);
        return $response;
    }

    /**
     * @return Redirect
     */
    private function noEntityFound()
    {
        $this->messageManager->addErrorMessage(__('There is no sample file for this entity.'));
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/');

        return $resultRedirect;
    }

    /**
     * @return Redirect
     */
    private function emptyModuleName()
    {
        $this->messageManager->addErrorMessage(__('Module Name is empty.'));
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/');

        return $resultRedirect;
    }
}
