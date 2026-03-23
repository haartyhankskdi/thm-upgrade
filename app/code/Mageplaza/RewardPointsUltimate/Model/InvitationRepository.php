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
use Magento\Contact\Controller\Index;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPointsUltimate\Api\Data\InvitationSearchResultInterfaceFactory as SearchResultFactory;
use Mageplaza\RewardPointsUltimate\Api\InvitationRepositoryInterface;
use Mageplaza\RewardPointsUltimate\Helper\Data;

/**
 * Class InvitationRepository
 * @package Mageplaza\RewardPointsUltimate\Model
 */
class InvitationRepository implements InvitationRepositoryInterface
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
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * InvitationRepository constructor.
     *
     * @param SearchResultFactory $searchResultFactory
     * @param Data $helperData
     * @param TransportBuilder $transportBuilder
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        Data $helperData,
        TransportBuilder $transportBuilder,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->helperData          = $helperData;
        $this->transportBuilder    = $transportBuilder;
        $this->customerRepository  = $customerRepository;
        $this->storeManager        = $storeManager;
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        $searchResult = $this->searchResultFactory->create();

        return $this->helperData->processGetList($searchCriteria, $searchResult);
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function invite($customerId, $sendFrom, $emails, $message)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        /**
         * @var Customer $customer
         */
        $customer = $this->customerRepository->getById($customerId);

        $this->sendInvitation($customer, $sendFrom, $emails, $message);

        return true;
    }

    /**
     * @param Customer $customer
     * @param string $sendFrom
     * @param string $emails
     * @param string $message
     *
     * @return bool
     * @throws InputException
     * @throws LocalizedException
     */
    public function sendInvitation($customer, $sendFrom, $emails, $message)
    {
        $emails = $this->validateEmail($emails);

        if (!in_array($sendFrom, ['store', 'email_address'], true)) {
            throw new InputException(__('%1 invalid!', $sendFrom));
        }

        if ($sendFrom === 'store') {
            $sender = $this->helperData->getConfigValue(Index::XML_PATH_EMAIL_SENDER);
        } else {
            $sender['name'] = $customer->getName();
            $sender['email'] = $customer->getEmail();
        }

        try {
            foreach ($emails as $email) {
                $transport = $this->transportBuilder->setTemplateIdentifier($this->helperData->getInvitationEmail())
                    ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId()])
                    ->setTemplateVars(
                        [
                            'message' => htmlspecialchars($message),
                            'url' => $this->helperData->getReferUrl(
                                $this->helperData->getCryptHelper()->encrypt($customer->getId())
                            )
                        ]
                    )
                    ->setFrom($sender)
                    ->addTo($email, '')
                    ->getTransport();

                $transport->sendMessage();
            }
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return true;
    }

    /**
     * @param string $emails
     *
     * @return array
     * @throws InputException
     */
    public function validateEmail($emails)
    {
        $emails = explode(',', $emails);
        $result = [];
        if (!$emails) {
            throw new InputException(__('Email required.'));
        }

        if (count($emails) > 20) {
            throw new InputException(__('Email list must be equals or less than 20.'));
        }

        foreach ($emails as $email) {
            $email = trim($email);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $result[] = $email;
            } else {
                throw new InputException(__('%1 is not a valid email address.', $email));
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function getReferralByEmail(SearchCriteriaInterface $searchCriteria, $email)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        $searchResult = $this->searchResultFactory->create()->addFieldToFilter('referral_email', $email);

        return $this->helperData->processGetList($searchCriteria, $searchResult);
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function getReferralByCustomerId(SearchCriteriaInterface $searchCriteria, $customerId)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        $customer = $this->helperData->getAccountHelper()->getCustomerById($customerId);

        return $this->getReferralByEmail($searchCriteria, $customer->getEmail());
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function getInvitedByEmail(SearchCriteriaInterface $searchCriteria, $email)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        $searchResult = $this->searchResultFactory->create()->addFieldToFilter('invited_email', $email);

        return $this->helperData->processGetList($searchCriteria, $searchResult);
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function referByCode($code)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        $accountHelper = $this->helperData->getAccountHelper();
        $referId = $this->helperData->getCryptHelper()->decrypt($code);
        $referer = $accountHelper->getCustomerById($referId);
        if (!$referer || !$referer->getId()) {
            throw new NoSuchEntityException(__('Referrer does not exist.'));
        }

        $cookieHelper = $this->helperData->getCookieHelper();
        $cookieHelper->set($code);

        return true;
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function getReferCode($customerId)
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        return $this->helperData->getCryptHelper()->encrypt($customerId);
    }

    /**
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function count()
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('The module is disabled'));
        }

        return $this->searchResultFactory->create()->getTotalCount();
    }
}
