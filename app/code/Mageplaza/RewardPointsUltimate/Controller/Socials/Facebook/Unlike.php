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

namespace Mageplaza\RewardPointsUltimate\Controller\Socials\Facebook;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Mageplaza\RewardPointsUltimate\Controller\Socials;
use Mageplaza\RewardPointsUltimate\Helper\Data;

/**
 * Class Unlike
 * @package Mageplaza\RewardPointsUltimate\Controller\Socials\Facebook
 */
class Unlike extends Socials
{
    /**
     * @param $customer
     * @param $transaction
     * @param $behavior
     * @param $url
     *
     * @return $this
     * @throws LocalizedException
     */
    public function processAction($customer, $transaction, $behavior, $url)
    {
        if (is_object($transaction)) {
            if ($transaction->getPointAmount()) {
                $unlikeTransaction = $this->_createTransaction(
                    $customer,
                    [
                        'point_amount' => -$transaction->getPointAmount(),
                        'extra_content' => ['page' => $url]
                    ],
                    false
                );
                if ($unlikeTransaction->getId()) {
                    $extraContent = Data::jsonDecode($transaction->getExtraContent());
                    $extraContent['unlike'] = 1;
                    $transaction->setExtraContent(Data::jsonEncode($extraContent))->save();
                }
            }
        }

        return $this;
    }

    /**
     * @param $customerId
     * @param $url
     *
     * @return bool
     */
    public function hasUrl($customerId, $url)
    {
        $hasUrl = false;
        $transactions = $this->helperData->getTransactionByFieldToFilter(
            [
                'customer_id' => $customerId,
                'action_code' => Data::ACTION_LIKE_FACEBOOK
            ],
            false
        );
        foreach ($transactions as $transaction) {
            $extraContent = $this->helperData->getExtraContent($transaction);
            if (isset($extraContent['page'])) {
                $isUnlike = isset($extraContent['unlike']);
                if ($extraContent['page'] == $url && !$isUnlike) {
                    $hasUrl = $transaction;

                    break;
                }
            }
        }

        return $hasUrl;
    }

    /**
     * @param $pointFormat
     *
     * @return Phrase
     */
    public function getCompleteMessageByAction($pointFormat)
    {
        return __('Unlike this page successfully!');
    }

    /**
     * @return string
     */
    public function getTransactionAction()
    {
        return Data::ACTION_UNLIKE_FACEBOOK;
    }
}
