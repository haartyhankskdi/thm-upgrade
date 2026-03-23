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

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\RewardPointsPro\Api\Data\SCSpendingRuleInterface;
use Mageplaza\RewardPointsPro\Api\Data\SCSpendingSearchResultInterfaceFactory as SearchResultFactory;
use Mageplaza\RewardPointsPro\Api\SCSpendingRuleRepositoryInterface;
use Mageplaza\RewardPointsPro\Helper\Data;
use Mageplaza\RewardPointsPro\Helper\Validate;
use Mageplaza\RewardPointsPro\Model\ResourceModel\ShoppingCartSpendingRule\Collection;
use Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\DiscountStyle;
use Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\OptionsSpending;
use Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\Type;

/**
 * Class SCSpendingRuleRepository
 * @package Mageplaza\RewardPointsPro\Model
 */
class SCSpendingRuleRepository implements SCSpendingRuleRepositoryInterface
{
    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var ShoppingCartSpendingRuleFactory
     */
    protected $shoppingCartSpendingRuleFactory;

    /**
     * @var Validate
     */
    protected $validate;

    /**
     * SCSpendingRuleRepository constructor.
     *
     * @param SearchResultFactory $searchResultFactory
     * @param Data $helperData
     * @param ShoppingCartSpendingRuleFactory $shoppingCartSpendingRuleFactory
     * @param Validate $validate
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        Data $helperData,
        ShoppingCartSpendingRuleFactory $shoppingCartSpendingRuleFactory,
        Validate $validate
    ) {
        $this->searchResultFactory             = $searchResultFactory;
        $this->helperData                      = $helperData;
        $this->shoppingCartSpendingRuleFactory = $shoppingCartSpendingRuleFactory;
        $this->validate                        = $validate;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function getList(?SearchCriteriaInterface $searchCriteria = null)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        /**
         * @var Collection $searchResult
         */
        $searchResult = $this->searchResultFactory->create();

        return $this->helperData->processGetList($searchCriteria, $searchResult);
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function delete($id)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        try {
            $rule = $this->getSpendingRuleById($id);
            $rule->delete();
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSpendingRuleById($id)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        $rule = $this->shoppingCartSpendingRuleFactory->create();
        $rule->load($id);
        if (!$rule->getId() || ((int) $rule->getRuleType() !== Type::SHOPPING_CART_SPENDING)) {
            throw new NoSuchEntityException(__('No such rule id!'));
        }

        return $rule;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     * @throws InputException
     */
    public function save(SCSpendingRuleInterface $rule)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        $ruleId    = $rule->getRuleId();
        $ruleModel = $ruleId ? $this->getSpendingRuleById($ruleId) : $this->shoppingCartSpendingRuleFactory->create();
        $ruleModel->addData($rule->getData());
        $data = $ruleModel->getData();
        $rule->setRuleType(Type::SHOPPING_CART_SPENDING);
        $requireFields = [
            ShoppingCartSpendingRule::NAME,
            ShoppingCartSpendingRule::IS_ACTIVE,
            ShoppingCartSpendingRule::CUSTOMER_GROUP_IDS,
            ShoppingCartSpendingRule::WEBSITE_IDS,
            ShoppingCartSpendingRule::ACTION,
            ShoppingCartSpendingRule::DISCOUNT_STYLE,
            ShoppingCartSpendingRule::APPLY_TO_SHIPPING,
            ShoppingCartSpendingRule::LABELS
        ];

        $yesNoFields  = [
            ShoppingCartSpendingRule::IS_ACTIVE,
            ShoppingCartSpendingRule::STOP_RULES_PROCESSING,
            ShoppingCartSpendingRule::APPLY_TO_SHIPPING
        ];
        $numberFields = [ShoppingCartSpendingRule::POINT_AMOUNT, ShoppingCartSpendingRule::DISCOUNT_AMOUNT];

        $requireFields = array_merge($requireFields, $numberFields);

        $this->validate->validateRequired($data, $requireFields);
        $this->validate->validateGeneral($data);
        foreach ($numberFields as $field) {
            $this->validate->validateGreaterThanZero($data, $field);
        }

        foreach ($yesNoFields as $field) {
            $this->validate->validateOptions([0, 1], $data, $field);
        }

        $this->validate->validateOptions(OptionsSpending::getOptionArray(), $data, ShoppingCartSpendingRule::ACTION);
        $this->validate->validateOptions(
            DiscountStyle::getOptionArray(),
            $data,
            ShoppingCartSpendingRule::DISCOUNT_STYLE
        );

        if ($rule->getMaxPoints()) {
            $this->validate->validateZeroOrGreater($data, ShoppingCartSpendingRule::MAX_POINTS);
        }

        if ($rule->getSortOrder()) {
            $this->validate->validateZeroOrGreater($data, ShoppingCartSpendingRule::SORT_ORDER);
        }

        $labels = [];
        foreach ($rule->getLabels() as $labelObj) {
            if ($this->validate->getStoreById($labelObj->getStoreId())) {
                $labels[$labelObj->getStoreId()] = $labelObj->getLabel();
            }
        }

        $data[ShoppingCartSpendingRule::LABELS] = $labels;
        $ruleModel->addData($data)->save();

        /**
         * Reload by id to get full data from table of new object
         */
        return $ruleModel->load($ruleModel->getRuleId());
    }
}
