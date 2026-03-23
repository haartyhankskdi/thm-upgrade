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

namespace Mageplaza\RewardPointsUltimate\Model\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Pattern
 * @package Mageplaza\RewardPointsUltimate\Model\Attribute\Backend
 */
class Pattern extends AbstractBackend
{
    const FIELD_MP_RW_CUSTOMER_GROUP = 'mp_rw_customer_group';

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Pattern constructor.
     *
     * @param ManagerInterface $messageManager
     */
    public function __construct(ManagerInterface $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $data          = $object->getData($attributeCode);
        if ($attributeCode === self::FIELD_MP_RW_CUSTOMER_GROUP && is_array($data) && $data) {
            $object->setData($attributeCode, implode(',', $data));
        } else {
            $object->setData($attributeCode, $data);
        }

        return parent::beforeSave($object);
    }
}
