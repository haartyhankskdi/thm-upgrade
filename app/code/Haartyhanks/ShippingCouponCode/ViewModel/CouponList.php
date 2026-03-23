<?php
namespace Haartyhanks\ShippingCouponCode\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory as CouponCollectionFactory;
use Magento\SalesRule\Model\RuleFactory;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

class CouponList implements ArgumentInterface
{
    protected $couponCollectionFactory;
    protected $ruleFactory;
      private $ruleCollectionFactory;

    public function __construct(
        CouponCollectionFactory $couponCollectionFactory,
           RuleCollectionFactory $ruleCollectionFactory,
        RuleFactory $ruleFactory
    ) {
        $this->couponCollectionFactory = $couponCollectionFactory;
        $this->ruleFactory = $ruleFactory;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
    }

    /**
     * Get latest active coupons
     *
     * @return array
     */
   public function getLatestCoupons()
{
     $collection = $this->ruleCollectionFactory->create();
        $collection->addFieldToFilter('is_active', 1);
        $collection->addFieldToFilter('coupon_type', ['neq' => 1]);
        $collection->setOrder('rule_id', 'DESC');
        $currentDate = (new \DateTime())->format('Y-m-d H:i:s');
    $collection->addFieldToFilter(
    ['from_date', 'from_date'],
    [
        ['null' => true],
        ['lteq' => $currentDate]
    ]
);

$collection->addFieldToFilter(
    ['to_date', 'to_date'],
    [
        ['null' => true],
        ['gteq' => $currentDate]
    ]
);
        $collection->setPageSize(2);
        return $collection;
    }


     
}