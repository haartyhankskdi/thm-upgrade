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
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Plugin\Model;

use Exception;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Mageplaza\RewardPointsUltimate\Helper\Data as ultimateData;
use Psr\Log\LoggerInterface;

/**
 * Class CreateRewardAccount
 * @package Mageplaza\RewardPointsUltimate\Plugin\Model
 */
class CreateRewardAccount
{
    /**
     * @var ultimateData
     */
    protected $ultimateData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CreateRewardAccount constructor.
     *
     * @param LoggerInterface $logger
     * @param ultimateData $ultimateData
     */
    public function __construct(
        LoggerInterface $logger,
        ultimateData $ultimateData
    ) {
        $this->logger       = $logger;
        $this->ultimateData = $ultimateData;
    }

    /**
     * @param AccountManagement $subject
     * @param CustomerInterface $customer
     * @param string $hash
     * @param string $redirectUrl
     *
     * @return array
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    public function beforeCreateAccountWithPasswordHash(
        AccountManagement $subject,
        CustomerInterface $customer,
        $hash,
        $redirectUrl = ''
    ) {
        if ($this->ultimateData->isEnabled()) {
            $referCodeOrEmail = trim($customer->getExtensionAttributes()->getMpRefer() ?: '');
            try {
                $referCode = $this->ultimateData->getCryptHelper()->checkReferCodeOrEmail($referCodeOrEmail);
            } catch (Exception $e) {
                $referCode = false;
            }

            if ($referCode) {
                $this->ultimateData->getCookieHelper()->set($referCode);
            } else {
                $this->ultimateData->getCookieHelper()->deleteMpRefererKeyFromCookie();
            }
        }

        return [$customer, $hash, $redirectUrl];
    }
}
