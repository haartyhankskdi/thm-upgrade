<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Controller\Adminhtml\Order;

use Amasty\Gdpr\Model\Anonymization\Anonymizer;
use Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;

class Anonymise extends BackendAction implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Amasty_Gdpr::order_anonymise';

    /**
     * @var Anonymizer
     */
    protected $anonymizer;

    public function __construct(
        Context $context,
        Anonymizer $anonymizer
    ) {
        parent::__construct($context);
        $this->anonymizer = $anonymizer;
    }

    public function execute()
    {
        $incrementId = $this->_request->getParam('increment_id');
        if (!$incrementId) {
            $this->messageManager->addWarningMessage(__(
                'It is impossible to anonymize an order. Order Increment Id is not defined'
            ));
        }

        if ($this->anonymizer->anonymizeOrder($incrementId)) {
            $this->messageManager->addSuccessMessage(__(
                'Order %1 is successfully anonymised.',
                $incrementId
            ));
        } else {
            $this->messageManager->addWarningMessage(__(
                'It is impossible to anonymize an order with this status type.'
            ));
        }

        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
