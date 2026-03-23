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

namespace Mageplaza\RewardPointsUltimate\Helper;

use Exception;
use Magento\Bundle\Model\Product\Type;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection as GroupCollection;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\System\Store;
use Mageplaza\RewardPoints\Model\Account;
use Mageplaza\RewardPoints\Model\AccountFactory;
use Mageplaza\RewardPoints\Model\ResourceModel\Account\CollectionFactory as AccountCollection;
use Mageplaza\RewardPointsPro\Helper\Data as RewardHelper;
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;
use Mageplaza\RewardPointsUltimate\Model\Milestone;
use Mageplaza\RewardPointsUltimate\Model\MilestoneFactory;
use Mageplaza\RewardPointsUltimate\Model\ResourceModel\Milestone\Collection;
use Mageplaza\RewardPointsUltimate\Model\ResourceModel\Milestone\CollectionFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;
use Mageplaza\RewardPointsUltimate\Model\Source\Status;
use Mageplaza\RewardPointsUltimate\Model\Source\UrlParam;

/**
 * Class Data
 * @package Mageplaza\RewardPointsUltimate\Helper
 */
class Data extends RewardHelper
{
    const BEHAVIOR_CONFIGURATION  = '/behavior';
    const MILESTONE_CONFIGURATION = '/milestone';
    const REFERRALS_CONFIGURATION = '/referrals';
    const DEFAULT_URL_PREFIX      = 'code';

    /**
     * @var Resolver
     */
    protected $resolver;

    /**
     * Transaction Action Code
     */
    const ACTION_SIGN_UP              = 'earning_sign_up';
    const ACTION_NEWSLETTER           = 'earning_newsletter_subscriber';
    const ACTION_REVIEW_PRODUCT       = 'earning_review_product';
    const ACTION_CUSTOMER_BIRTHDAY    = 'earning_customer_birthday';
    const ACTION_CUSTOMER_COMEBACK    = 'earning_customer_comeback';
    const ACTION_SEND_EMAIL_TO_FRIEND = 'earning_send_email_to_friend';
    const ACTION_LIKE_FACEBOOK        = 'earning_like_facebook';
    const ACTION_UNLIKE_FACEBOOK      = 'earning_unlike_facebook';
    const ACTION_TWEET_TWITTER        = 'earning_tweet_twitter';
    const ACTION_SHARE_FACEBOOK       = 'earning_share_facebook';
    const ACTION_SELL_POINTS          = 'sell_points_order';
    const ACTION_SELL_POINTS_REFUND   = 'sell_points_order_refund';
    const ACTION_REFERRAL_EARNING     = 'referral_earning';
    const ACTION_REFERRAL_REFUND      = 'referral_refund';
    const ACTION_IMPORT_TRANSACTION   = 'import_transaction';
    const ACTION_POINTS_ALLOCATION    = 'earning_points_allocation';
    const ACTION_LIFETIME_AMOUNT      = 'earning_lifetime_amount';
   //const ACTION_CUSTOMER_SURVEY      = 'earning_customer_survey';

    /**
     * @var BehaviorFactory
     *
     */
    protected $behaviorFactory;

    /**
     * @var Store
     */
    protected $systemStore;

    /**
     * @var array
     */
    protected $storesOptionHash = [];

    /**
     * @var array
     */
    protected $websitesOptionHash = [];

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var MilestoneFactory
     */
    protected $milestoneFactory;

    /**
     * @var AccountFactory
     */
    protected $account;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var AccountCollection
     */
    protected $accountFactory;

    /**
     * @var GroupCollection
     */
    protected $customerGroup;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $timeZone
     * @param SessionFactory $sessionFactory
     * @param DesignInterface $theme
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param BehaviorFactory $behaviorFactory
     * @param Resolver $resolver
     * @param CollectionFactory $collectionFactory
     * @param Store $store
     * @param MilestoneFactory $milestoneFactory
     * @param AccountFactory $account
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param TransportBuilder $transportBuilder
     * @param AccountCollection $accountFactory
     * @param GroupCollection $customerGroup
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $timeZone,
        SessionFactory $sessionFactory,
        DesignInterface $theme,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        BehaviorFactory $behaviorFactory,
        Resolver $resolver,
        CollectionFactory $collectionFactory,
        Store $store,
        MilestoneFactory $milestoneFactory,
        AccountFactory $account,
        CustomerRepositoryInterface $customerRepositoryInterface,
        TransportBuilder $transportBuilder,
        AccountCollection $accountFactory,
        GroupCollection $customerGroup
    ) {
        $this->behaviorFactory             = $behaviorFactory;
        $this->resolver                    = $resolver;
        $this->systemStore                 = $store;
        $this->collectionFactory           = $collectionFactory;
        $this->milestoneFactory            = $milestoneFactory;
        $this->sessionFactory              = $sessionFactory;
        $this->account                     = $account;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->transportBuilder            = $transportBuilder;
        $this->accountFactory              = $accountFactory;
        $this->customerGroup               = $customerGroup;

        parent::__construct(
            $context,
            $objectManager,
            $storeManager,
            $priceCurrency,
            $timeZone,
            $sessionFactory,
            $theme,
            $searchCriteriaBuilder
        );
    }

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getConfigBehavior($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . self::BEHAVIOR_CONFIGURATION . $code, $storeId);
    }

    /**
     * @param string $type
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEnabledSocialButton($type, $storeId = null)
    {
        return $this->getConfigBehavior($type . '/enabled', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getFacebookButtonCount($storeId = null)
    {
        return $this->getConfigBehavior('facebook/show_count', $storeId);
    }

    /**
     * @param string $type
     * @param null $storeId
     *
     * @return mixed
     */
    public function getSocialPageDisplay($type, $storeId = null)
    {
        return $this->getConfigBehavior($type . '/pages_display', $storeId);
    }

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getConfigReferrals($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . self::REFERRALS_CONFIGURATION . $code, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getInvitationEmail($storeId = null)
    {
        return $this->getConfigReferrals('general/email', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getDefaultReferUrl($storeId = null)
    {
        return $this->getConfigReferrals('general/default_url', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getURLParam($storeId = null)
    {
        return $this->getConfigReferrals('url_key/param', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getURLPrefix($storeId = null)
    {
        return $this->getConfigReferrals('url_key/prefix', $storeId);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getCurrentUrl()
    {
        return $this->storeManager->getStore()->getCurrentUrl(false);
    }

    /***
     * @return array
     */
    public function getStores()
    {
        if (!$this->storesOptionHash) {
            $this->storesOptionHash = $this->systemStore->getStoreOptionHash();
        }

        return $this->storesOptionHash;
    }

    /***
     * @return array
     */
    public function getWebsites()
    {
        if (!$this->websitesOptionHash) {
            $this->websitesOptionHash = $this->systemStore->getWebsiteOptionHash();
        }

        return $this->websitesOptionHash;
    }

    /**
     * @return bool
     */
    public function canUseStoreSwitcherLayoutByMpReports()
    {
        if ($this->isModuleOutputEnabled('Mageplaza_Reports')) {
            $mpReportModule = $this->objectManager->create(\Mageplaza\Reports\Helper\Data::class);

            return $mpReportModule->isEnabled();
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isDisabledFilters()
    {
        return !$this->canUseStoreSwitcherLayoutByMpReports();
    }

    /**
     * @param string $action
     * @param array $changeHtml
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPointHtml($action, $changeHtml = [])
    {
        $pointAction = $this->behaviorFactory->create()->getPointByAction($action);
        $html        = '';
        if ($this->isEnabled() && $pointAction > 0) {
            $pointHelper = $this->getPointHelper();
            $html        = '<div class="mp-reward-earning" style="margin: 5px 0 5px 0">';
            $html        .= $pointHelper->getIconHtml();
            $html        .= '<span style="margin-left: 5px">' . $this->replaceMessage(
                    $action,
                    '<strong>' . $pointHelper->format($pointAction) . '</strong>'
                ) . '</span>';
            $html        .= '</div>';
            if (isset($changeHtml['result']) && $changeHtml['query']) {
                $html = $this->changeHtmlWithDOM($changeHtml['result'], $changeHtml['query'], $html);
            }
        }

        return $html;
    }

    /**
     * @param array $changeHtml
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getSubscribePointHtml($changeHtml = [])
    {
        return $this->getPointHtml(CustomerEvents::NEWSLETTER, $changeHtml);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProductReviewPointHtml()
    {
        return $this->getPointHtml(CustomerEvents::PRODUCT_REVIEW);
    }

    /**
     * @param string $action
     * @param int $point
     *
     * @return mixed
     */
    public function replaceMessage($action, $point)
    {
        $messages = [
            CustomerEvents::NEWSLETTER        => __('Earn %1 for subscribing to newsletter', $point),
            CustomerEvents::PRODUCT_REVIEW    => __('Earn %1 for writing a review for this product', $point),
            CustomerEvents::SIGN_UP           => __('Earn %1 for registering an account', $point),
            CustomerEvents::CUSTOMER_BIRTHDAY => __('Earn %1 on your birthday', $point)
        ];
        if (isset($messages[$action])) {
            return $messages[$action];
        }

        return '';
    }

    /**
     * @param array $filters
     * @param bool $orderBy
     * @param bool $isFirstItem
     *
     * @return AbstractCollection
     */
    public function getTransactionByFieldToFilter($filters, $orderBy = false, $isFirstItem = false)
    {
        $transactions = $this->getTransaction()->getCollection();
        foreach ($filters as $field => $value) {
            $transactions->addFieldToFilter($field, $value);
        }
        if ($orderBy) {
            $transactions->setOrder('transaction_id', $orderBy);
        }
        if ($isFirstItem) {
            $transactions->getFirstItem();
        }

        return $transactions;
    }

    /**
     * @param array $filters
     * @param bool $orderBy
     * @param bool $isFirstItem
     * @param array $conditions
     * @param bool $isGetPointAmount
     *
     * @return int
     */
    public function getTransactionByFilter(
        $filters,
        $orderBy = false,
        $isFirstItem = false,
        $conditions = [],
        $isGetPointAmount = false
    ) {
        $pointAmount  = 0;
        $transactions = $this->getTransactionByFieldToFilter($filters, $orderBy, $isFirstItem);
        foreach ($transactions as $transaction) {
            $extraContent          = $this->getExtraContent($transaction);
            $extraContentCondition = $extraContent[$conditions['field']];
            if (isset($extraContentCondition) && $extraContentCondition == $conditions['value'] && !$isGetPointAmount) {
                return $transaction;
            }
            $pointAmount += $transaction->getPointAmount();
        }

        return $pointAmount;
    }

    /**
     * @param array $filters
     * @param bool $orderBy
     * @param bool $isFirstItem
     * @param array $conditions
     *
     * @return int
     */
    public function getTransactionByFilterToReview(
        $filters,
        $orderBy = false,
        $isFirstItem = false,
        $conditions = []
    ) {
        $transactions = $this->getTransactionByFieldToFilter($filters, $orderBy, $isFirstItem);
        foreach ($transactions as $transaction) {
            $extraContent          = $this->getExtraContent($transaction);
            $extraContentCondition = $extraContent[$conditions['field']];
            if (isset($extraContentCondition) && $extraContentCondition == $conditions['value']) {
                return $transaction;
            }
        }

        return false;
    }

    /**
     * @param Object $transaction
     *
     * @return array|mixed
     */
    public function getExtraContent($transaction)
    {
        if ($transaction->getExtraContent()) {
            return self::jsonDecode($transaction->getExtraContent());
        }

        return [];
    }

    /**
     * @param string $url
     * @param string $shareUrl
     *
     * @return string
     */
    public function getTwitterButton($url, $shareUrl, $isBindEvent = true)
    {
        $html
            = '<div class="mp-rw-social twitter-earning" style ="float:left;  margin:5px;">
                     <a href="https://twitter.com/share" class="twitter-share-button"
                        data-lang="en"
                        data-url="' . $shareUrl . '">' . __('Tweet') . ' </a>
                </div>';
        if ($isBindEvent) {
            $html
                .= '<script>
                            twttr.ready(function (twttr) {
                                twttr.events.bind("click", function (event) {
                                    mpSocials.sendAjax("' . $url . '",event.target.dataset.url);
                                });
                            });
                        </script>';
        }

        return $html;
    }

    /**
     * @return string
     */
    public function getTwitterScript()
    {
        return '
            <script>
                    //<![CDATA[
                        window.twttr = (function (d, s, id) {
                            var t, js, fjs = d.getElementsByTagName(s)[0];
                            if (d.getElementById(id)) return;
                            js = d.createElement(s);
                            js.id = id;
                            js.src = "https://platform.twitter.com/widgets.js";
                            fjs.parentNode.insertBefore(js, fjs);
                            return window.twttr || (t = {_e: [], ready: function (f) {
                                t._e.push(f)
                            }});
                        }(document, "script", "twitter-wjs"));
                    //]]>
            </script>
        ';
    }

    /**
     * @param string $appId
     *
     * @return string
     */
    public function getFacebookScript($appId = '')
    {
        return '<script>
                //<![CDATA[
                (function (d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) return;
                    js = d.createElement(s);
                    js.id = id;
                    js.src = "//connect.facebook.net/' . $this->resolver->getLocale() . '/all.js#xfbml=1&version=v2.0'
            . $appId . '";
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, "script", "facebook-jssdk"));
                //]]>
            </script>';
    }

    /**
     * @return Cookie
     */
    public function getCookieHelper()
    {
        return $this->objectManager->get(Cookie::class);
    }

    /**
     * @return mixed
     */
    public function getCryptHelper()
    {
        return $this->objectManager->get(Crypt::class);
    }

    /**
     * @param string $code
     *
     * @return string
     */
    public function getReferUrl($code)
    {
        $prefix   = $this->getURLPrefix() ?: self::DEFAULT_URL_PREFIX;
        $urlParam = "?$prefix=" . $code;
        if ($this->getURLParam() == UrlParam::HASH) {
            $urlParam = '#' . $prefix . $code;
        }

        return $this->_urlBuilder->getUrl() . $urlParam;
    }

    /**
     * @param Order $order
     * @param Invoice|Creditmemo $object
     * @param string $action
     *
     * @throws LocalizedException
     */
    public function calculateReferralPoints($order, $object, $action)
    {
        $referralEarn = 0;
        $id           = $object->getOrigData('entity_id');
        if ($id === null) {
            foreach ($object->getItems() as $item) {
                $orderItem      = $item->getOrderItem();
                $mpReferralEarn = $orderItem->getMpRewardReferralEarn();
                if ($orderItem->getProductType() == Type::TYPE_CODE || !$mpReferralEarn) {
                    continue;
                }
                $referralEarn += ($mpReferralEarn * $item->getQty()) / $orderItem->getQtyOrdered();
            }
        }

        if ($object instanceof Creditmemo) {
            $referralEarn = -$referralEarn;
        }

        if ($referralEarn) {
            $this->addTransaction(
                $action,
                $order->getMpRewardReferralId(),
                $referralEarn,
                $order
            );
        }
    }

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getMilestoneConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . self::MILESTONE_CONFIGURATION . $code, $storeId);
    }

    /**
     * @param int $groupId
     * @param int $totalOrder
     * @param int $websiteId
     *
     * @return Collection
     */
    public function getTierCollectionByCustomerGroup($groupId, $totalOrder, $websiteId = 0)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        if (!$this->storeManager->isSingleStoreMode()) {
            $collection->addFieldToFilter('website_ids', ['finset' => $websiteId]);
        }
        $collection->addFieldToFilter('customer_group_ids', ['finset' => $groupId])
            ->addFieldToFilter('status', ['eq' => Status::ENABLE])
            ->addFieldToFilter('sum_order', ['lteq' => $totalOrder]);

        return $collection;
    }

    /**
     * @return false|string
     */
    public function getPeriodDate()
    {
        $period = $this->getMilestoneConfig('period');

        if (!empty($period)) {
            $from = strtotime('-' . $period . ' day', strtotime(date('Y-m-d')));

            return date('Y-m-d', $from);
        }

        return '';
    }

    /**
     * @return string
     */
    public function getSourceMilestoneAction()
    {
        $source = '';
        foreach (explode(',', $this->getMilestoneConfig('source')) as $sourceId) {
            switch ($sourceId) {
                case 0:
                    $source .= 'admin';
                    break;
                case 1:
                    $source .= 'earning_customer_birthday';
                    break;
                case 2:
                    $source .= 'earning_order';
                    break;
                case 3:
                    $source .= 'earning_review_product';
                    break;
                case 4:
                    $source .= 'earning_sign_up';
                    break;
                case 5:
                    $source .= 'earning_newsletter_subscriber';
                    break;
                case 6:
                    $source .= 'earning_like_facebook';
                    break;
                case 7:
                    $source .= 'earning_share_facebook';
                    break;
                case 8:
                    $source .= 'earning_tweet_twitter';
                    break;
                case 9:
                    $source .= 'referral_earning';
                    break;
                case 10:
                    $source .= 'earning_customer_comeback';
                    break;
                case 11:
                    $source .= 'earning_send_email_to_friend';
                    break;
                case 12:
                    $source .= 'earning_refund';
                    break;
                case 13:
                    $source .= 'earning_lifetime_amount';
                    break;
                    /*case 14:
                    $source .= 'earning_customer_survey';
                    break;  */
            }
            $source .= ',';
        }

        return trim($source, ',');
    }

    /**
     * @param int $customerId
     * @param null $account
     *
     * @return false
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updateTier($customerId, $account = null)
    {
        if (!$this->getMilestoneConfig('enabled')) {
            return false;
        }

        /** @var Account $account */
        if (!$account) {
            $account = $this->account->create()->loadByCustomerId($customerId);
        }
        /** @var Milestone $currentTier */
        $currentTier = $this->milestoneFactory->create()->loadByCustomerId($customerId);
        $updateTier  = false;
        try {
            $customer        = $this->getCustomerById($customerId);
            $customerGroupId = $customer->getGroupId();
            $websiteId       = $customer->getWebsiteId();
        } catch (Exception $e) {
            $customerGroupId = 0;
            $websiteId       = 0;
        }

        $collection = $this->getTierCollectionByCustomerGroup(
            $customerGroupId,
            $account->getTotalOrder(),
            $websiteId
        );
        $collection->setOrder('min_point', DataCollection::SORT_ORDER_ASC);
        $isUpdate       = false;
        $source         = $this->getSourceMilestoneAction();
        $milestonePoint = $account->getMilestoneTotalEarningPoints($source, $this->getPeriodDate()) ?: 0;

        foreach ($collection->getItems() as $tier) {
            if ($milestonePoint >= $tier->getMinPoint()) {
                $updateTier = $tier;
                $isUpdate   = true;
            }
        }

        if ($isUpdate && $updateTier) {
            if ($currentTier->getId()) {
                if ($currentTier->getId() !== $updateTier->getId()) {
                    $updateTier->upTier($customerId);
                    $this->sendMilestoneEmail($account, $updateTier);
                }
            } else {
                $updateTier->addTier($customerId);
                $this->sendMilestoneEmail($account, $updateTier);
            }
        } elseif (($collection->getFirstItem()->getMinPoint() > $milestonePoint || !$collection->count())
            && $currentTier->getId()
        ) {
            $currentTier->deleteTier($customerId);
        }

        return true;
    }

    /**
     * @param int $customerId
     *
     * @return CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomerById($customerId)
    {
        return $this->customerRepositoryInterface->getById($customerId);
    }

    /**
     * @param Account $account
     * @param Object $tier
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function sendMilestoneEmail($account, $tier)
    {
        if (!$this->getMilestoneConfig('enabled') || !$this->getMilestoneConfig('email')) {
            return false;
        }

        $customer = $this->customerRepositoryInterface->getById($account->getCustomerId());

        $transport = $this->transportBuilder->setTemplateIdentifier($this->getMilestoneConfig('template'))
            ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId()])
            ->setTemplateVars(
                [
                    'tier_name'      => $tier->getName(),
                    'current_point'  => $account->getMilestoneTotalEarningPoints(
                        $this->getSourceMilestoneAction(),
                        $this->getPeriodDate()
                    ),
                    'benefits_rate'  => $tier->getEarnPercent(),
                    'benefits_rule'  => $tier->getEarnFixed(),
                    'benefits_spent' => $tier->getSpentPercent()
                ]
            )
            ->setFrom('general')
            ->addTo($customer->getEmail(), $customer->getFirstname())
            ->getTransport();

        try {
            $transport->sendMessage();
        } catch (MailException $e) {
            return false;
        }

        return true;
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updateTierCustomer()
    {
        /** @var \Mageplaza\RewardPoints\Model\ResourceModel\Account\Collection $collection */
        $collection = $this->accountFactory->create();

        foreach ($collection->getItems() as $account) {
            $this->updateTier($account->getCustomerId(), $account);
        }
    }

    /**
     * @param int $customerId
     *
     * @return int|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getGroupIdByCustomerId($customerId)
    {
        return $this->customerRepositoryInterface->getById($customerId)->getGroupId();
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * @throws Exception
     */
    public function updateCustomerGroupAndWebsiteForBaseMileStone()
    {
        $customerGroup = [];
        $websiteIds    = [];

        foreach ($this->customerGroup->toOptionArray() as $item) {
            if ($item['value'] !== '0') {
                $customerGroup[] = $item['value'];
            }
        }

        foreach ($this->storeManager->getWebsites() as $website) {
            $websiteIds[] = $website->getId();
        }

        $post = $this->milestoneFactory->create()->load(1, 'tier_id');

        $post->setData('customer_group_ids', implode(',', $customerGroup))
            ->setData('website_ids', implode(',', $websiteIds))
            ->save();
    }
}
