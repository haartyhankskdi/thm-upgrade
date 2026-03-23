<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\ResourceModel\ConsentQueue\Grid;

use Amasty\Gdpr\Model\ResourceModel\Grid\AbstractSearchResult;

class Collection extends AbstractSearchResult
{
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
