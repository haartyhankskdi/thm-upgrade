<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Policy;

use Amasty\Gdpr\Model\Policy\DataProvider\PolicyPopupDataProvider;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class PopupData extends Action
{
    /**
     * @var PolicyPopupDataProvider
     */
    private $policyPopupDataProvider;

    public function __construct(
        PolicyPopupDataProvider $policyPopupDataProvider,
        Context $context
    ) {
        parent::__construct($context);
        $this->policyPopupDataProvider = $policyPopupDataProvider;
    }

    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result = $this->policyPopupDataProvider->getData();

        return $resultJson->setData($result);
    }
}
