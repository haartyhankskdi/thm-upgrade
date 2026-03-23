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

namespace Mageplaza\RewardPointsUltimate\Model\Action\Import;

use Magento\Backend\Model\Auth;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPoints\Model\Action;

/**
 * Class Transaction
 * @package Mageplaza\RewardPointsUltimate\Model\Action\Import
 */
class Transaction extends Action
{
    const CODE = 'import_transaction';

    /**
     * @var Auth
     */
    protected $_auth;

    /**
     * Transaction constructor.
     *
     * @param Data $helper
     * @param Auth $auth
     * @param null $customer
     * @param null $actionObject
     * @param array $data
     */
    public function __construct(
        Data $helper,
        Auth $auth,
        $customer = null,
        $actionObject = null,
        array $data = []
    ) {
        $this->_auth = $auth;

        parent::__construct($helper, $customer, $actionObject, $data);
    }

    /**
     * @inheritdoc
     */
    public function getActionLabel()
    {
        return __('Admin Import');
    }

    /**
     * @inheritdoc
     */
    public function getTitle($transaction)
    {
        $extraContent = Data::jsonDecode($transaction->getData('extra_content'));
        if (isset($extraContent['comment'])) {
            return $extraContent['comment'];
        }

        return __('Import by %1', $this->_auth->getUser()->getName());
    }

    /**
     * @return int|mixed
     */
    public function getActionType()
    {
        return Data::ACTION_TYPE_ADMIN;
    }

    /**
     * @inheritdoc
     */
    protected function getExtraContent()
    {
        $extraContent = parent::getExtraContent();

        if ($comment = $this->getActionObject()->getData('comment')) {
            $extraContent['comment'] = $comment;
        }

        $extraContent['admin_id'] = $this->_auth->getUser()->getUserId();

        return $extraContent;
    }
}
