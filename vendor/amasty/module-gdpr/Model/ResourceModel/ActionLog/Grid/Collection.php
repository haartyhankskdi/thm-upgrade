<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\ResourceModel\ActionLog\Grid;

class Collection extends \Amasty\Gdpr\Model\ResourceModel\Grid\AbstractSearchResult
{
    /**
     * @var array
     */
    protected $_map = ['fields' => ['created_at' => 'main_table.created_at']];

    /**
     * Init collection select
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _initSelect()
    {
        parent::_initSelect();
        $this->joinCustomerData();
        $this->addFilterToMap(
            'name',
            new \Zend_Db_Expr("CONCAT_WS(' ', prefix, firstname, middlename, lastname, suffix)")
        );

        return $this;
    }
}
