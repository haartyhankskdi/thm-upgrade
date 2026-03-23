<?php

namespace Ebizmarts\SagePaySuite\Plugin\HealthCheck;

use Ebizmarts\SagePaySuite\Controller\Adminhtml\Repeat\Request;
use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Framework\Controller\ResultFactory;

class Repeat extends HealthCheck
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    public function __construct(
        ResultFactory $resultFactory,
        Data $suiteHelper,
        Config $config
    ) {
        $this->resultFactory = $resultFactory;
        parent::__construct(
            $suiteHelper,
            $config
        );
    }
    public function aroundExecute(Request $subject, callable $proceed)
    {
        if ($this->isValid()) {
            $result = $proceed();
        } else {
            $responseContent = [
                'success' => false,
                'error_message' => __('Something went wrong: %1', HealthCheck::ERROR_MESSAGE),
            ];
            $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $result->setData($responseContent);
        }
        return $result;
    }
}
