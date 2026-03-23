<?php

namespace Ebizmarts\BrippoPayments\Block\Express;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Model\Express;
use Ebizmarts\BrippoPayments\Helper\Express as ExpressHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;

class PaymentElementFailsafe extends Template
{
    /** @var DataHelper */
    protected $dataHelper;

    /** @var Express */
    protected $expressPaymentMethod;

    /** @var ExpressHelper */
    protected $expressHelper;

    /** @var Logger */
    protected $logger;

    /** @var Registry */
    protected $registry;

    public function __construct(
        Template\Context $context,
        DataHelper $dataHelper,
        Express $expressPaymentMethod,
        Logger $logger,
        Registry $registry,
        ExpressHelper $expressHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->expressPaymentMethod = $expressPaymentMethod;
        $this->logger = $logger;
        $this->registry = $registry;
        $this->expressHelper = $expressHelper;
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getIsEnabled(): bool
    {
        $storeId = $this->_storeManager->getStore()->getId();

        if (!$this->expressPaymentMethod->isAvailable()) {
            return false;
        }

        if (!$this->dataHelper->isServiceReady($storeId)) {
            return false;
        }

        return true;
    }

    /**
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getAccountId(): ?string
    {
        $storeId = $this->_storeManager->getStore()->getId();
        return $this->dataHelper->getAccountId(
            $storeId,
            $this->dataHelper->isLiveMode($storeId)
        );
    }
}
