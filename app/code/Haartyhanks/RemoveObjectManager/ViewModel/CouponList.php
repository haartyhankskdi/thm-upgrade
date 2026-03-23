<?php
declare(strict_types=1);

namespace Haartyhanks\RemoveObjectManager\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

class CouponList implements ArgumentInterface
{
    /**
     * @var RuleCollectionFactory
     */
    private $ruleCollectionFactory;

    public function __construct(
        RuleCollectionFactory $ruleCollectionFactory
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
    }

    public function getActiveCoupons()
    {
        $collection = $this->ruleCollectionFactory->create();
        $collection->addFieldToFilter('is_active', 1);
        $collection->addFieldToFilter('coupon_type', ['neq' => 1]);
        $collection->setOrder('rule_id', 'DESC');
        $collection->setPageSize(2);
        return $collection;
    }   
}