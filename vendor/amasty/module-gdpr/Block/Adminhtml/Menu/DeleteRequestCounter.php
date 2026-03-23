<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Block\Adminhtml\Menu;

use Amasty\Gdpr\Api\Data\DeleteRequestInterface;
use Amasty\Gdpr\Model\DeleteRequest;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\CollectionFactory as DeleteRequestCollectionFactory;
use Magento\Backend\Block\Template;

class DeleteRequestCounter extends Template
{
    /**
     * @var DeleteRequestCollectionFactory
     */
    private $deleteRequestCollectionFactory;

    public function __construct(
        Template\Context $context,
        DeleteRequestCollectionFactory $deleteRequestCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->deleteRequestCollectionFactory = $deleteRequestCollectionFactory;
    }

    /**
     * @return int
     */
    public function getDeleteRequestsCount()
    {
        return $this->deleteRequestCollectionFactory->create()->addFieldToFilter(
            DeleteRequestInterface::APPROVED,
            ['neq' => DeleteRequest::IS_APPROVED]
        )->getSize();
    }
}
