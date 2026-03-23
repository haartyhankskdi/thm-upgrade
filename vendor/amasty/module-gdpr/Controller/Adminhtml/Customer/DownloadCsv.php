<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml\Customer;

use Amasty\Gdpr\Controller\Result\File;
use Amasty\Gdpr\Controller\Result\FileFactory;
use Amasty\Gdpr\Model\CustomerData;
use Magento\Backend\App\Action;
use Magento\Customer\Controller\AbstractAccount as AbstractAccountAction;

class DownloadCsv extends AbstractAccountAction
{
    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var CustomerData
     */
    private $customerData;

    public function __construct(
        Action\Context $context,
        FileFactory $fileFactory,
        CustomerData $customerData
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->customerData = $customerData;
    }

    public function execute()
    {
        $mergeIntoOneFile = false;
        $customerId = (int)$this->getRequest()->getParam('customerId');
        $data = $this->customerData->getPersonalData($customerId, $mergeIntoOneFile);

        return $this->fileFactory->create(
            [
                'fileName' => 'personal-data',
                'fileExtension' => $mergeIntoOneFile ? File::CSV : File::ZIP,
                'data' => $data
            ]
        );
    }
}
