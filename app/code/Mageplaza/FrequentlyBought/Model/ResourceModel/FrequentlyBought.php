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
 * @package     Mageplaza_FrequentlyBought
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FrequentlyBought\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Zend_Db_Expr;

/**
 * Class FrequentlyBought
 * @package Mageplaza\FrequentlyBought\Model\ResourceModel
 */
class FrequentlyBought extends AbstractDb
{

    /**
     * Define main table name and attributes table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageplaza_fbt_product_link', 'link_id');
    }

    /**
     * Delete product link by link_id
     *
     * @param int $linkId
     *
     * @return int
     * @throws LocalizedException
     */
    public function deleteProductLink($linkId)
    {
        return $this->getConnection()->delete($this->getMainTable(), ['link_id = ?' => $linkId]);
    }

    /**
     * Save Product Links process
     *
     * @param int $parentId
     * @param array $data
     *
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function saveProductLinks($parentId, $data)
    {
        if (!is_array($data)) {
            $data = [];
        }

        $connection = $this->getConnection();

        $bind = [':product_id' => (int)$parentId];
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['linked_product_id', 'link_id']
        )->where(
            'product_id = :product_id'
        );

        $links = $connection->fetchPairs($select, $bind);
        foreach ($data as $linkedProductId => $linkInfo) {
            if (!isset($links[$linkedProductId])) {
                $bind = [
                    'product_id' => $parentId,
                    'linked_product_id' => $linkedProductId,
                    'position' => $linkInfo['position']
                ];
                $connection->insert($this->getMainTable(), $bind);
            }
        }

        return $this;
    }

    /**
     * Retrieve product link_id by link product id
     *
     * @param int $parentId
     * @param int $linkedProductId
     *
     * @return string
     * @throws LocalizedException
     */
    public function getProductLinkId($parentId, $linkedProductId)
    {
        $connection = $this->getConnection();

        $bind = [
            ':product_id' => (int)$parentId,
            ':linked_product_id' => (int)$linkedProductId
        ];
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['link_id']
        )->where(
            'product_id = :product_id'
        )->where(
            'linked_product_id = :linked_product_id'
        );

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Check if product has links.
     *
     * @param int $parentId ID of product
     *
     * @return bool
     * @throws LocalizedException
     */
    public function hasProductLinks($parentId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['count' => new Zend_Db_Expr('COUNT(*)')]
        )->where('product_id = :product_id');

        return $connection->fetchOne($select, ['product_id' => $parentId]) > 0;
    }

    /**
     * Retrieve parent ids array by required child
     *
     * @param int|array $childId
     *
     * @return string[]
     * @throws LocalizedException
     */
    public function getParentIdsByChild($childId)
    {
        $parentIds = [];
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['product_id', 'linked_product_id']
        )->where(
            'linked_product_id IN(?)',
            $childId
        );

        $result = $connection->fetchAll($select);
        foreach ($result as $row) {
            $parentIds[] = $row['product_id'];
        }

        return $parentIds;
    }

    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param int $parentId
     *
     * @return array
     * @throws LocalizedException
     */
    public function getChildrenIds($parentId)
    {
        $connection = $this->getConnection();
        $childrenIds = [];
        $bind = [':product_id' => (int)$parentId];
        $select = $connection->select()->from(
            ['l' => $this->getMainTable()],
            ['linked_product_id']
        )->where(
            'product_id = :product_id'
        );

        $result = $connection->fetchAll($select, $bind);
        foreach ($result as $row) {
            $childrenIds[] = $row['linked_product_id'];
        }

        return $childrenIds;
    }
}
