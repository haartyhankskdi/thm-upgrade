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
use Mageplaza\RewardPointsPro\Api\Data\SCEarningRuleInterface;
use Mageplaza\RewardPointsPro\Api\Data\SCEarningSearchResultInterfaceFactory as SearchResultFactory;
use Mageplaza\RewardPointsPro\Api\SCEarningRuleRepositoryInterface;
use Mageplaza\RewardPointsPro\Helper\Data;
use Mageplaza\RewardPointsPro\Helper\Validate;
use Mageplaza\RewardPointsPro\Model\ResourceModel\ShoppingCartEarningRule\Collection;
use Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\Actions;
use Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\Type;

/**
 * Class SCEarningRuleRepository
 * @package Mageplaza\RewardPointsPro\Model
 */
class SCEarningRuleRepository implements SCEarningRuleRepositoryInterface
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
     * @var ShoppingCartEarningRule
     */
    protected $shoppingCartEarningRule;

    /**
     * @var Validate
     */
    protected $validate;

    /**
     * SCEarningRuleRepository constructor.
     *
     * @param SearchResultFactory $searchResultFactory
     * @param Data $helperData
     * @param ShoppingCartEarningRuleFactory $shoppingCartEarningRule
     * @param Validate $validate
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        Data $helperData,
        ShoppingCartEarningRuleFactory $shoppingCartEarningRule,
        Validate $validate
    ) {
        $this->searchResultFactory     = $searchResultFactory;
        $this->helperData              = $helperData;
        $this->shoppingCartEarningRule = $shoppingCartEarningRule;
        $this->validate                = $validate;
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
            $rule = $this->getEarningRuleById($id);
            $rule->delete();
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function getEarningRuleById($id)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        $rule = $this->shoppingCartEarningRule->create();
        $rule->load($id);
        if (!$rule->getId() || ((int) $rule->getRuleType() !== Type::SHOPPING_CART_EARNING)) {
            throw new NoSuchEntityException(__('No such rule id!'));
        }

        return $rule;
    }

    /**
     * {@inheritdoc}
     * @throws InputException
     * @throws LocalizedException
     */
    public function save(SCEarningRuleInterface $rule)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        $ruleId    = $rule->getRuleId();
        $ruleModel = $ruleId ? $this->getEarningRuleById($ruleId) : $this->shoppingCartEarningRule->create();
        $ruleModel->addData($rule->getData());
        $data = $ruleModel->getData();
        $rule->setRuleType(Type::SHOPPING_CART_EARNING);
        $requireFields = [
            ShoppingCartEarningRule::NAME,
            ShoppingCartEarningRule::IS_ACTIVE,
            ShoppingCartEarningRule::CUSTOMER_GROUP_IDS,
            ShoppingCartEarningRule::WEBSITE_IDS,
            ShoppingCartEarningRule::ACTION
        ];

        $yesNoFields  = [
            ShoppingCartEarningRule::IS_ACTIVE,
            ShoppingCartEarningRule::STOP_RULES_PROCESSING,
            ShoppingCartEarningRule::APPLY_TO_SHIPPING
        ];
        $numberFields = [ShoppingCartEarningRule::POINT_AMOUNT];

        $action = (int) $rule->getAction();
        if ($action === Actions::TYPE_PRICE) {
            $numberFields[] = ShoppingCartEarningRule::MONEY_STEP;
        }
        if ($action === Actions::TYPE_QTY) {
            unset($yesNoFields[ShoppingCartEarningRule::APPLY_TO_SHIPPING]);
            $numberFields[] = ShoppingCartEarningRule::QTY_STEP;
        }

        $requireFields = array_merge($requireFields, $numberFields);

        $this->validate->validateRequired($data, $requireFields);

        $this->validate->validateGeneral($data);
        foreach ($numberFields as $field) {
            $this->validate->validateGreaterThanZero($data, $field);
        }

        foreach ($yesNoFields as $field) {
            $this->validate->validateOptions([0, 1], $data, $field);
        }

        $this->validate->validateOptions(Actions::getOptionArray(), $data, ShoppingCartEarningRule::ACTION);
        if ($rule->getSortOrder()) {
            $this->validate->validateZeroOrGreater($data, ShoppingCartEarningRule::SORT_ORDER);
        }
        if ($rule->getMaxPoints()) {
            $this->validate->validateZeroOrGreater($data, ShoppingCartEarningRule::MAX_POINTS);
        }

        $ruleModel->addData($data)->save();

        /**
         * Reload by id to get full data from table of new object
         */
        return $ruleModel->load($ruleModel->getRuleId());
    }
}
