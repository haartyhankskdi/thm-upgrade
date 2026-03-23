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
use Magento\Config\Model\Config\Source\Email\Template;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\RewardPointsPro\Helper\Validate;
use Mageplaza\RewardPointsUltimate\Api\BehaviorRepositoryInterface;
use Mageplaza\RewardPointsUltimate\Api\Data\BehaviorInterface;
use Mageplaza\RewardPointsUltimate\Api\Data\BehaviorSearchResultInterfaceFactory as SearchResultFactory;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\ResourceModel\Behavior\Collection;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;
use Mageplaza\RewardPointsUltimate\Model\Source\PointPeriod;

/**
 * Class BehaviorRepository
 * @package Mageplaza\RewardPointsUltimate\Model
 */
class BehaviorRepository implements BehaviorRepositoryInterface
{
    const DEFAULT_TEMPLATE_PATH = 'rewardpoints/email/birthday/template';

    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory;

    /**
     * @var BehaviorFactory
     */
    protected $behavior;

    /**
     * @var Validate
     */
    protected $validate;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Template
     */
    protected $emailTemplate;

    /**
     * BehaviorRepository constructor.
     *
     * @param SearchResultFactory $searchResultFactory
     * @param BehaviorFactory $behavior
     * @param Validate $validate
     * @param Data $helperData
     * @param Template $emailTemplate
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        BehaviorFactory $behavior,
        Validate $validate,
        Data $helperData,
        Template $emailTemplate
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->behavior            = $behavior;
        $this->validate            = $validate;
        $this->helperData          = $helperData;
        $this->emailTemplate       = $emailTemplate;
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

        $rule = $this->behavior->create();
        $rule->load($id);
        if (!$rule->getId()) {
            throw new NoSuchEntityException(__('No such rule id!'));
        }

        return $rule;
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function getEmailTemplateOptions()
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        return $this->emailTemplate->setPath(self::DEFAULT_TEMPLATE_PATH)->toOptionArray();
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function getSenderOptions()
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        /**
         * Base logic Magento\Config\Model\Config\Source\Email\Identity can't get options
         * Use get config value to process on this case
         */
        $section = $this->helperData->getConfigValue('trans_email');
        $options = [];
        foreach ($section as $idGroup => $group) {
            $options[] = [
                'value' => preg_replace('#^ident_(.*)$#', '$1', $idGroup),
                'label' => $group['name'],
            ];
        }

        return $options;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function toOptionArray($options)
    {
        $optionsArray = [];
        foreach ($options as $option) {
            $optionsArray[$option['value']] = $option['label'];
        }

        return $optionsArray;
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     * @throws InputException
     */
    public function save(BehaviorInterface $rule)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        $ruleId    = $rule->getRuleId();
        $ruleModel = $ruleId ? $this->getRuleById($ruleId) : $this->behavior->create();
        $ruleModel->addData($rule->getData());
        $data = $ruleModel->getData();

        $requireFields = [
            Behavior::NAME,
            Behavior::IS_ACTIVE,
            Behavior::CUSTOMER_GROUP_IDS,
            Behavior::WEBSITE_IDS,
            Behavior::POINT_ACTION,
            Behavior::ACTION,
            Behavior::POINT_AMOUNT
        ];

        $yesNoFields = [Behavior::IS_ACTIVE];

        $events = [
            CustomerEvents::LIKE_PAGE_WITH_FACEBOOK,
            CustomerEvents::TWEET_PAGE_WITH_TWITTER
        ];

        $this->validate->validateRequired($data, $requireFields);
        $data['isUseGuest'] = 1;
        $this->validate->validateGeneral($data);

        $pointActions = (int) $ruleModel->getPointAction();
        if ($pointActions === CustomerEvents::PRODUCT_REVIEW) {
            $this->validate->validateRequired($data, [Behavior::MIN_WORDS, Behavior::MAX_POINT_PERIOD]);
            $this->validate->validateOptions(PointPeriod::getOptionArray(), $data, Behavior::MAX_POINT_PERIOD);
            $yesNoFields[] = Behavior::IS_PURCHASED;
        }

        if (in_array($pointActions, array_merge(
            $events,
            [CustomerEvents::PRODUCT_REVIEW, CustomerEvents::SHARE_PURCHASE_FACEBOOK]
        ), true)) {
            $this->validate->validateOptions(PointPeriod::getOptionArray(), $data, Behavior::MAX_POINT_PERIOD);
            if ($ruleModel->getMaxPoint()) {
                $this->validate->validateGreaterThanZero($data, Behavior::MAX_POINT);
            }
        }

        if ($pointActions === CustomerEvents::CUSTOMER_BIRTHDAY) {
            $yesNoFields[] = Behavior::IS_ENABLED_EMAIL;
            $options       = $this->toOptionArray($this->getSenderOptions());
            $emailTemplate = $this->toOptionArray($this->getEmailTemplateOptions());
            $this->validate->validateOptions($options, $data, Behavior::SENDER);
            if ($ruleModel->getEmailTemplate()) {
                $this->validate->validateOptions($emailTemplate, $data, Behavior::EMAIL_TEMPLATE);
            } else {
                $ruleModel->setEmailTemplate(self::DEFAULT_TEMPLATE_PATH);
            }
        }

        if ($rule->getSortOrder()) {
            $this->validate->validateZeroOrGreater($data, Behavior::SORT_ORDER);
        }

        foreach ($yesNoFields as $field) {
            $this->validate->validateOptions([0, 1], $data, $field);
        }

        if ($ruleModel->getMinInterval() && in_array($pointActions, $events, true)) {
            $this->validate->validateGreaterThanZero($data, Behavior::MIN_INTERVAL);
        }

        if ($pointActions === CustomerEvents::SHARE_PURCHASE_FACEBOOK) {
            $this->validate->validateRequired($data, [Behavior::FB_APP_ID]);
        }

        $ruleModel->save();

        /**
         * Reload by id to get full data from table of new object
         */
        return $ruleModel->load($ruleModel->getRuleId());
    }
}
