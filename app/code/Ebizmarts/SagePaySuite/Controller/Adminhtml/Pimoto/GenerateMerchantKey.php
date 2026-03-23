<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Adminhtml\Pimoto;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Backend\Model\Session\Quote as AdminCheckoutSession;

class GenerateMerchantKey implements ActionInterface
{
    /** @var \Ebizmarts\SagePaySuite\Model\PiMsk */
    private $piMsk;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var ResultFactory
     */
    private $resultFactory;
    private $quoteSession;

    /**
     * @param \Ebizmarts\SagePaySuite\Model\PiMsk $piMsk
     * @param ManagerInterface $manager
     * @param ResultFactory $resultFactory
     * @param AdminCheckoutSession $quoteSession
     */
    public function __construct(
        \Ebizmarts\SagePaySuite\Model\PiMsk $piMsk,
        ManagerInterface $manager,
        ResultFactory $resultFactory,
        AdminCheckoutSession $quoteSession
    ) {
        $this->piMsk = $piMsk;
        $this->messageManager = $manager;
        $this->resultFactory = $resultFactory;
        $this->quoteSession = $quoteSession;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $quote = $this->_getSession()->getQuote();

        /** @var \Ebizmarts\SagePaySuite\Api\Data\ResultInterface $result */
        $result = $this->piMsk->getSessionKey($quote, null, true);

        if ($result->getSuccess() === false) {
            $this->messageManager->addErrorMessage(__('Something went wrong: %1', $result->getErrorMessage()));
        }

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result->__toArray());
        return $resultJson;
    }

    /**
     * Retrieve session object
     *
     * @return \Magento\Backend\Model\Session\Quote
     */
    protected function _getSession()
    {
        return $this->quoteSession;
    }
}
