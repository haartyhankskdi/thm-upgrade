<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\ResourceModel\Policy\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    public function _initSelect()
    {
        parent::_initSelect();

        $nameExpression = new \Zend_Db_Expr("CONCAT_WS(' ', firstname, lastname)");

        $this->addFilterToMap('last_edit_name', $nameExpression);

        $this->getSelect()
            ->joinLeft(
                ['u' => $this->getTable('admin_user')],
                'main_table.last_edited_by = u.user_id',
                [
                    'last_edit_name' => $nameExpression
                ]
            );

        return $this;
    }
}
