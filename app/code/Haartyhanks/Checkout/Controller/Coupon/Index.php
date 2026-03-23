<?php

namespace Haartyhanks\Checkout\Controller\Coupon;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    protected $ruleCollection;

    public function __construct(
        Context $context,
        CollectionFactory $ruleCollection
    ) {
        $this->ruleCollection = $ruleCollection;
        parent::__construct($context);
    }

    public function execute()
{
    $collection = $this->ruleCollection->create()
        ->addFieldToFilter('is_active', 1)
        ->addFieldToFilter('coupon_type', ['neq' => 1])
        ->setOrder('rule_id', 'DESC')   
        ->setPageSize(2); 
    $data = [];
    foreach ($collection as $rule) {
        $code = $rule->getCode();
        if (!$code) continue;

        $data[] = [
            'code'        => $code,
            'name'        => $rule->getName(),
            'description' => $rule->getDescription()
        ];
    }

    return $this->resultFactory
        ->create(ResultFactory::TYPE_JSON)
        ->setData($data);
}

}