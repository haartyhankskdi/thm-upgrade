<?php

namespace Ebizmarts\BrippoPayments\Controller\Payments;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Recover extends Action
{
    protected $logger;
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Logger $logger
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context         $context,
        Logger          $logger,
        PageFactory     $resultPageFactory
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
