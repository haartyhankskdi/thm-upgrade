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
use Mageplaza\RewardPointsPro\Api\CatalogRuleRepositoryInterface;
use Mageplaza\RewardPointsPro\Api\Data\CatalogRuleInterface;
use Mageplaza\RewardPointsPro\Api\Data\CatalogRuleSearchResultInterfaceFactory as SearchResultFactory;
use Mageplaza\RewardPointsPro\Helper\Data;
use Mageplaza\RewardPointsPro\Helper\Validate;
use Mageplaza\RewardPointsPro\Model\ResourceModel\CatalogRule\Collection;
use Mageplaza\RewardPointsPro\Model\Source\Catalogrule\Earning as CatalogRuleAction;

/**
 * Class CatalogRuleRepository
 * @package Mageplaza\RewardPointsPro\Model
 */
class CatalogRuleRepository implements CatalogRuleRepositoryInterface
{
    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory = null;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var CatalogRuleFactory
     */
    protected $catalogRuleFactory;

    /**
     * @var Validate
     */
    protected $validate;

    /**
     * CatalogRuleRepository constructor.
     *
     * @param SearchResultFactory $searchResultFactory
     * @param Data $helperData
     * @param CatalogRuleFactory $catalogRuleFactory
     * @param Validate $validate
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        Data $helperData,
        CatalogRuleFactory $catalogRuleFactory,
        Validate $validate
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->helperData          = $helperData;
        $this->catalogRuleFactory  = $catalogRuleFactory;
        $this->validate            = $validate;
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
     * @inheritDoc
     * @throws LocalizedException
     */
    public function getCatalogRuleById($id)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        $catalogRule = $this->catalogRuleFactory->create();
        $catalogRule->load($id);
        if (!$catalogRule->getId()) {
            throw new NoSuchEntityException(__('No such entity id!'));
        }

        return $catalogRule;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function delete($id)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        try {
            $catalogRule = $this->getCatalogRuleById($id);
            $catalogRule->delete();
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     * @throws InputException
     */
    public function save(CatalogRuleInterface $rule)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        $ruleId    = $rule->getRuleId();
        $ruleModel = $ruleId ? $this->getCatalogRuleById($ruleId) : $this->catalogRuleFactory->create();
        $ruleModel->addData($rule->getData());
        $data          = $ruleModel->getData();
        $requireFields = [
            CatalogRule::NAME,
            CatalogRule::IS_ACTIVE,
            CatalogRule::CUSTOMER_GROUP_IDS,
            CatalogRule::WEBSITE_IDS,
            CatalogRule::ACTION,
            CatalogRule::POINT_AMOUNT
        ];

        $yesNoFields  = [CatalogRule::IS_ACTIVE, CatalogRule::STOP_RULES_PROCESSING];
        $numberFields = [CatalogRule::POINT_AMOUNT];
        if (in_array($rule->getAction(), [CatalogRuleAction::TYPE_PRICE, CatalogRuleAction::TYPE_PROFIT], true)) {
            $requireFields[] = CatalogRule::MONEY_STEP;
            $numberFields[]  = CatalogRule::MONEY_STEP;
            if ($rule->getMaxPoints()) {
                $this->validate->validateZeroOrGreater($data, CatalogRule::MAX_POINTS);
            }
        }

        $this->validate->validateRequired($data, $requireFields);
        $data['isUseGuest'] = 1;
        $this->validate->validateGeneral($data);
        foreach ($numberFields as $field) {
            $this->validate->validateGreaterThanZero($data, $field);
        }

        $this->validate->validateOptions(CatalogRuleAction::getOptionArray(), $data, CatalogRule::ACTION);
        foreach ($yesNoFields as $field) {
            $this->validate->validateOptions([0, 1], $data, $field);
        }

        if ($rule->getSortOrder()) {
            $this->validate->validateZeroOrGreater($data, CatalogRule::SORT_ORDER);
        }

        $ruleModel->addData($data)->save();

        return $ruleModel->load($ruleModel->getRuleId());
    }
}
