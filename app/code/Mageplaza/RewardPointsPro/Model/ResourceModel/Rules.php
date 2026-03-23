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
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Rule\Model\ResourceModel\AbstractResource;

/**
 * Class Rules
 * @package Mageplaza\RewardPointsPro\Model\ResourceModel
 */
abstract class Rules extends AbstractResource
{
    /**
     * @var array
     */
    protected $_associatedEntitiesMap;

    /**
     * @var string
     */
    protected $associatedEntityMapVirtual = 'Mageplaza\RewardPointsPro\Model\ResourceModel\ShoppingCart\AssociatedEntityMap';

    /**
     * Rules constructor.
     *
     * @param Context $context
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        $connectionName = null
    ) {
        $this->_associatedEntitiesMap = $this->getAssociatedEntitiesMap();

        parent::__construct($context, $connectionName);
    }

    /**
     * @return array
     */
    private function getAssociatedEntitiesMap()
    {
        if (!$this->_associatedEntitiesMap) {
            $this->_associatedEntitiesMap = ObjectManager::getInstance()
                ->get($this->associatedEntityMapVirtual)
                ->getData();
        }

        return $this->_associatedEntitiesMap;
    }
}
