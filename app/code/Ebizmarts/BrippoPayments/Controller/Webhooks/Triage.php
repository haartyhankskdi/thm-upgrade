<?php

namespace Ebizmarts\BrippoPayments\Controller\Webhooks;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Ebizmarts\BrippoPayments\Helper\Webhook as WebhookHelper;
use Ebizmarts\BrippoPayments\Plugin\CsrfFilter;

class Triage extends Action
{
    /** @var Logger */
    protected $logger;

    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var WebhookHelper */
    protected $webhookHelper;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        WebhookHelper $webhookHelper,
        Logger $logger,
        CsrfFilter $csrfFilter
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->webhookHelper = $webhookHelper;
        $this->logger = $logger;
        $csrfFilter->filterCrsfInterfaceImplementation($this);
    }

    public function execute()
    {
        $this->webhookHelper->lock();
        $this->webhookHelper->dispatchEvent();
        $this->webhookHelper->unlock();
    }

    protected function _isAllowed(): bool
    {
        return true;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
