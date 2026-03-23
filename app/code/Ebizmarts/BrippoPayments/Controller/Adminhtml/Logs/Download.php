<?php

namespace Ebizmarts\BrippoPayments\Controller\Adminhtml\Logs;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;

class Download extends Action
{
    protected $fileFactory;

    public function __construct(
        Context $context,
        FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
    }

    public function execute()
    {
        $filepath = 'log/brippo_payments.log';
        $downloadedFileName = 'brippo_payments.log';
        $content['type'] = 'filename';
        $content['value'] = $filepath;
        return $this->fileFactory->create($downloadedFileName, $content, DirectoryList::VAR_DIR);
    }

    protected function _isAllowed(): bool
    {
        return true;
    }
}
