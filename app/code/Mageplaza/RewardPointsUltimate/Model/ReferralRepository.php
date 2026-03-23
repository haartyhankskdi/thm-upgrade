<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Model;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\RewardPointsPro\Helper\Validate;
use Mageplaza\RewardPointsUltimate\Api\Data\ReferralInterface;
use Mageplaza\RewardPointsUltimate\Api\Data\ReferralSearchResultInterfaceFactory as SearchResultFactory;
use Mageplaza\RewardPointsUltimate\Api\ReferralRepositoryInterface;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\ResourceModel\Referral\Collection;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerActions;
use Mageplaza\RewardPointsUltimate\Model\Source\ReferralActions;

/**
 * Class ReferralRepository
 * @package Mageplaza\RewardPointsUltimate\Model
 */
class ReferralRepository implements ReferralRepositoryInterface
{
    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory;

    /**
     * @var ReferralFactory
     */
    protected $referralFactory;

    /**
     * @var Validate
     */
    protected $validate;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * ReferralRepository constructor.
     *
     * @param SearchResultFactory $searchResultFactory
     * @param ReferralFactory $referralFactory
     * @param Validate $validate
     * @param Data $helperData
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        ReferralFactory $referralFactory,
        Validate $validate,
        Data $helperData
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->referralFactory     = $referralFactory;
        $this->validate            = $validate;
        $this->helperData          = $helperData;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function delete($id)
    {
        try {
            $rule = $this->getRuleById($id);
            $rule->delete();
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return true;
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function getRuleById($id)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        $rule = $this->referralFactory->create();
        $rule->load($id);
        if (!$rule->getId()) {
            throw new NoSuchEntityException(__('No such rule id!'));
        }

        return $rule;
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     * @throws InputException
     */
    public function save(ReferralInterface $rule)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        $ruleId    = $rule->getRuleId();
        $ruleModel = $ruleId ? $this->getRuleById($ruleId) : $this->referralFactory->create();
        $ruleModel->addData($rule->getData());
        $data = $ruleModel->getData();

        $requireFields = [
            Referral::NAME,
            Referral::IS_ACTIVE,
            Referral::CUSTOMER_GROUP_IDS,
            Referral::WEBSITE_IDS,
            Referral::REFERRAL_POINTS,
            Referral::REFERRAL_GROUP_IDS,
            Referral::CUSTOMER_ACTION,
            Referral::REFERRAL_TYPE
        ];
        $numberFields  = [Referral::REFERRAL_POINTS];
        $yesNoFields   = [Referral::IS_ACTIVE, Referral::CUSTOMER_APPLY_TO_SHIPPING, Referral::STOP_RULES_PROCESSING];

        $pointAction = $ruleModel->getCustomerAction();
        if (in_array($pointAction, [CustomerActions::TYPE_FIXED_POINTS, CustomerActions::TYPE_PRICE], true)) {
            $requireFields[] = Referral::CUSTOMER_POINTS;
        } else {
            $numberFields[] = Referral::CUSTOMER_DISCOUNT;
        }

        if ($pointAction === CustomerActions::TYPE_PRICE) {
            $numberFields[] = Referral::CUSTOMER_MONEY_STEP;
        }

        if ($rule->getReferralType() === ReferralActions::TYPE_PRICE) {
            $numberFields[] = Referral::REFERRAL_MONEY_STEP;
        }

        $requireFields = array_merge($requireFields, $numberFields);

        $this->validate->validateRequired($data, $requireFields);
        foreach ($numberFields as $field) {
            $this->validate->validateGreaterThanZero($data, $field);
        }
        $this->validate->validateGeneral($data);
        $this->validate->validateCustomerGroupIds($data, Referral::REFERRAL_GROUP_IDS);
        $this->validate->validateOptions(CustomerActions::getOptionArray(), $data, Referral::CUSTOMER_ACTION);
        $this->validate->validateOptions(ReferralActions::getOptionArray(), $data, Referral::REFERRAL_TYPE);

        foreach ($yesNoFields as $field) {
            $this->validate->validateOptions([0, 1], $data, $field);
        }

        $ruleModel->save();

        /**
         * Reload by id to get full data from table of new object
         */
        return $ruleModel->load($ruleModel->getRuleId());
    }
}
