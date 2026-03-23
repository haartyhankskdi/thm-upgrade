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
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Model\Export;

use Exception;
use Magento\Customer\Model\Customer\Attribute\Source\Store;
use Magento\Customer\Model\Customer\Attribute\Source\Website;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;

/**
 * Class AttributeCollection
 * @package Mageplaza\RewardPointsUltimate\Model\Export
 */
class AttributeCollection extends Collection
{
    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var array
     */
    protected $mapLabels
        = [
            'Customer Email',
            'Point Balance',
            'Store ID',
            'Website ID',
            'Notification Update',
            'Notification Expire',
            'Is Active'
        ];

    const CUSTOMER_EMAIL = 'email';
    const POINT_BALANCE = 'point_balance';
    const STORE_ID = 'store_id';
    const WEBSITE_ID = 'website_id';
    const NOTIFICATION_UPDATE = 'notification_update';
    const NOTIFICATION_EXPIRE = 'notification_expire';
    const IS_ACTIVE = 'is_active';

    /**
     * AttributeCollection constructor.
     *
     * @param EntityFactoryInterface $entityFactory
     * @param AttributeFactory $attributeFactory
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        AttributeFactory $attributeFactory
    ) {
        $this->attributeFactory = $attributeFactory;
        parent::__construct($entityFactory);
    }

    /**
     * @param array $attributes
     *
     * @return $this
     * @throws Exception
     */
    public function buildAttributes($attributes = [])
    {
        foreach ($attributes as $key => $attribute) {
            /** @var Attribute $attributeModel */
            $attributeModel = $this->attributeFactory->create();
            $attributeModel->setId($attribute);

            switch ($attribute) {
                case self::WEBSITE_ID:
                    $attributeModel->setSourceModel(Website::class);
                    $attributeModel->setFrontendInput('select');

                    break;
                case self::STORE_ID:
                    $attributeModel->setSourceModel(Store::class);
                    $attributeModel->setFrontendInput('select');

                    break;
                case self::CUSTOMER_EMAIL:
                    $attributeModel->setBackendType('varchar');

                    break;
                case self::NOTIFICATION_UPDATE:
                case self::NOTIFICATION_EXPIRE:
                case self::IS_ACTIVE:
                    $attributeModel->setSourceModel(Boolean::class);
                    $attributeModel->setFrontendInput('boolean');

                    break;
                default:
                    $attributeModel->setBackendType('int');
            }

            $attributeModel->setDefaultFrontendLabel($this->mapLabels[$key]);
            $attributeModel->setAttributeCode($attribute);
            $this->addItem($attributeModel);
        }

        return $this;
    }
}
