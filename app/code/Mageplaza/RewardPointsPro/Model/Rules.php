<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Model;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\CatalogRule\Model\Rule\Action\CollectionFactory;
use Magento\CatalogRule\Model\Rule\Condition\CombineFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Rule\Model\AbstractModel;
use Magento\SalesRule\Model\Rule\Condition\CombineFactory as SaleRuleCombineFactory;
use Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory as SaleRuleProductCombineFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPoints\Helper\Point;
use Mageplaza\RewardPointsPro\Api\Data\RuleInterface;
use Mageplaza\RewardPointsPro\Model\Indexer\Rule\RuleProductProcessor;

/**
 * Class Rules
 * @package Mageplaza\RewardPointsPro\Model
 */
abstract class Rules extends AbstractModel implements RuleInterface
{
    /**
     * @var ProductCollection
     */
    protected $productCollectionFactory;

    /**
     * @var Iterator
     */
    protected $resourceIterator;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CombineFactory
     */
    protected $conditionCombine;

    /**
     * @var CollectionFactory
     */
    protected $actionCollectionFactory;

    /**
     * @var SaleRuleCombineFactory
     */
    protected $saleRuleCombineFactory;

    /**
     * @var SaleRuleProductCombineFactory
     */
    protected $saleRuleProductCombineFactory;

    /**
     * @var RuleProductProcessor
     */
    protected $_ruleProductProcessor;

    /**
     * @var SessionFactory
     */
    protected $_customerSession;

    /**
     * @var \Mageplaza\RewardPointsPro\Helper\Data
     */
    protected $helperData;

    /**
     * Store already validated addresses and validation results
     * @var array
     */
    protected $_validatedAddresses = [];

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Point
     */
    protected $pointHelper;

    /**
     * Rules constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param ProductCollection $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param CombineFactory $combineFactory
     * @param CollectionFactory $actionCollectionFactory
     * @param SaleRuleCombineFactory $saleRuleCombineFactory
     * @param SaleRuleProductCombineFactory $saleRuleProductCombineFactory
     * @param ProductFactory $productFactory
     * @param SessionFactory $customerSession
     * @param Data $helperData
     * @param RuleProductProcessor $ruleProductProcessor
     * @param Iterator $resourceIterator
     * @param PriceCurrencyInterface $priceCurrency
     * @param RequestInterface $request
     * @param CustomerFactory $customerFactory
     * @param Point $pointHelper
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        ProductCollection $productCollectionFactory,
        StoreManagerInterface $storeManager,
        CombineFactory $combineFactory,
        CollectionFactory $actionCollectionFactory,
        SaleRuleCombineFactory $saleRuleCombineFactory,
        SaleRuleProductCombineFactory $saleRuleProductCombineFactory,
        ProductFactory $productFactory,
        SessionFactory $customerSession,
        Data $helperData,
        RuleProductProcessor $ruleProductProcessor,
        Iterator $resourceIterator,
        PriceCurrencyInterface $priceCurrency,
        RequestInterface $request,
        CustomerFactory $customerFactory,
        Point $pointHelper,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productCollectionFactory      = $productCollectionFactory;
        $this->resourceIterator              = $resourceIterator;
        $this->productFactory                = $productFactory;
        $this->storeManager                  = $storeManager;
        $this->conditionCombine              = $combineFactory;
        $this->actionCollectionFactory       = $actionCollectionFactory;
        $this->saleRuleCombineFactory        = $saleRuleCombineFactory;
        $this->_ruleProductProcessor         = $ruleProductProcessor;
        $this->saleRuleProductCombineFactory = $saleRuleProductCombineFactory;
        $this->_customerSession              = $customerSession;
        $this->helperData                    = $helperData;
        $this->priceCurrency                 = $priceCurrency;
        $this->request                       = $request;
        $this->customerFactory               = $customerFactory;
        $this->pointHelper                   = $pointHelper;

        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Get conditions field set id
     *
     * @param string $formName
     *
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * @param string $formName
     *
     * @return string
     * @since 100.1.0
     */
    public function getActionsFieldSetId($formName = '')
    {
        return $formName . 'rule_actions_fieldset_' . $this->getId();
    }

    /**
     * Get conditions instance
     * @return mixed
     */
    public function getConditionsInstance()
    {
        return $this->saleRuleCombineFactory->create();
    }

    /**
     * Get actions instance
     * @return mixed
     */
    public function getActionsInstance()
    {
        return $this->saleRuleProductCombineFactory->create();
    }

    /**
     * @param $resource
     * @param $field
     */
    public function bindRuleToEntity($resource, $field)
    {
        if ($data = $this->getData($field)) {
            if (!is_array($data)) {
                $data = explode(',', (string) $data);
            }
            $resource->bindRuleToEntity($this->getRuleId(), $data, substr($field, 0, -4));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        $this->bindRuleToEntity($this->getResource(), 'website_ids');
        $this->bindRuleToEntity($this->getResource(), 'customer_group_ids');
        parent::afterSave();
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        if (!$this->hasCustomerGroupIds()) {
            $customerGroupIds = $this->_getResource()->getCustomerGroupIds($this->getId());
            $this->setData('customer_group_ids', (array) $customerGroupIds);
        }
        if (!$this->hasWebsiteIds()) {
            $customerGroupIds = $this->_getResource()->getWebsiteIds($this->getId());
            $this->setData('website_ids', (array) $customerGroupIds);
        }

        parent::_afterLoad();
    }

    /**
     * Check cached validation result for specific address
     *
     * @param Address $address
     *
     * @return bool
     */
    public function hasIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);

        return isset($this->_validatedAddresses[$addressId]);
    }

    /**
     * Set validation result for specific address to results cache
     *
     * @param Address $address
     * @param bool $validationResult
     *
     * @return $this
     */
    public function setIsValidForAddress($address, $validationResult)
    {
        $addressId                             = $this->_getAddressId($address);
        $this->_validatedAddresses[$addressId] = $validationResult;

        return $this;
    }

    /**
     * Get cached validation result for specific address
     *
     * @param Address $address
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);

        return isset($this->_validatedAddresses[$addressId]) ?: false;
    }

    /**
     * Return id for address
     *
     * @param Address $address
     *
     * @return string
     */
    private function _getAddressId($address)
    {
        if ($address instanceof Address) {
            return $address->getId();
        }

        return $address;
    }

    /**
     * @param $item
     *
     * @return bool
     */
    public function validateRule($item)
    {
        if (!$this->canProcessRule($item->getAddress())) {
            return false;
        }

        if (!$this->getActions()->validate($item)) {
            $childItems = $item->getChildren();
            $isContinue = true;
            if (!empty($childItems)) {
                foreach ($childItems as $childItem) {
                    if ($this->getActions()->validate($childItem)) {
                        $isContinue = false;
                    }
                }
            }
            if ($isContinue) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if rule can be applied for specific address/quote/customer
     *
     * @param Address $address
     *
     * @return bool
     */
    public function canProcessRule($address)
    {
        if ($this->hasIsValidForAddress($address) && !$address->isObjectNew()) {
            return $this->getIsValidForAddress($address);
        }

        $this->afterLoad();

        /**
         * quote does not meet rule's conditions
         */
        if (!$this->validate($address)) {
            $this->setIsValidForAddress($address, false);

            return false;
        }

        /**
         * passed all validations, remember to be valid
         */
        $this->setIsValidForAddress($address, true);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getRuleId()
    {
        return $this->getData(self::RULE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setRuleId($value)
    {
        return $this->setData(self::RULE_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($value)
    {
        return $this->setData(self::NAME, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($value)
    {
        return $this->setData(self::DESCRIPTION, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getFromDate()
    {
        return $this->getData(self::FROM_DATE) ?: '';
    }

    /**
     * {@inheritdoc}
     */
    public function setFromDate($value)
    {
        return $this->setData(self::FROM_DATE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getToDate()
    {
        return $this->getData(self::TO_DATE) ?: '';
    }

    /**
     * {@inheritdoc}
     */
    public function setToDate($value)
    {
        return $this->setData(self::TO_DATE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($value)
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionsSerialized()
    {
        return $this->getData(self::CONDITIONS_SERIALIZED);
    }

    /**
     * {@inheritdoc}
     */
    public function setConditionsSerialized($value)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getActionsSerialized()
    {
        return $this->getData(self::ACTIONS_SERIALIZED);
    }

    /**
     * {@inheritdoc}
     */
    public function setActionsSerialized($value)
    {
        return $this->setData(self::ACTIONS_SERIALIZED, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getStopRulesProcessing()
    {
        return $this->getData(self::STOP_RULES_PROCESSING);
    }

    /**
     * {@inheritdoc}
     */
    public function setStopRulesProcessing($value)
    {
        return $this->setData(self::STOP_RULES_PROCESSING, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function setSortOrder($value)
    {
        return $this->setData(self::SORT_ORDER, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getAction()
    {
        return $this->getData(self::ACTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setAction($value)
    {
        return $this->setData(self::ACTION, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function setWebsiteIds(array $value)
    {
        return $this->setData(self::WEBSITE_IDS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteIds()
    {
        return $this->getData(self::WEBSITE_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroupIds()
    {
        return $this->getData(self::CUSTOMER_GROUP_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerGroupIds(array $value)
    {
        return $this->setData(self::CUSTOMER_GROUP_IDS, $value);
    }
}
