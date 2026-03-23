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

namespace Mageplaza\RewardPointsUltimate\Block\Referral;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;
use Mageplaza\RewardPointsUltimate\Model\InvitationFactory;

/**
 * Class Invitee
 * @package Mageplaza\RewardPoints\Block\Account
 */
class Invitee extends Template
{
    /**
     * @var InvitationFactory
     */
    protected $invitationFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var string
     */
    protected $invitees;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Invitee constructor.
     *
     * @param Context $context
     * @param InvitationFactory $invitationFactory
     * @param Session $customerSession
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Context $context,
        InvitationFactory $invitationFactory,
        Session $customerSession,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->invitationFactory = $invitationFactory;
        $this->customerSession   = $customerSession;
        $this->priceCurrency     = $priceCurrency;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getTransactions()) {
            $pager = $this->getLayout()->createBlock(Pager::class, 'mpreward.invitee.pager')
                ->setCollection($this->getTransactions());
            $this->setChild('pager', $pager);
        }

        return $this;
    }

    /**
     * Retrieve formated price
     *
     * @param float $value
     *
     * @return string
     */
    public function formatPrice($value)
    {
        return $this->priceCurrency->format(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get Invitees
     * @return array
     */
    public function getInvitees()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        if (!$customerId) {
            return [];
        }

        if (!$this->invitees) {
            $this->invitees = $this->invitationFactory->create()
                ->getCollection()
                ->addFieldToFilter('referral_id', $customerId);
        }

        return $this->invitees;
    }
}
