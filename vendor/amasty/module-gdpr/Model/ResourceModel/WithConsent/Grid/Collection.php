<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\ResourceModel\WithConsent\Grid;

class Collection extends \Amasty\Gdpr\Model\ResourceModel\Grid\AbstractSearchResult
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

        $this->addFilterToMap('website_id', 'main_table.website_id');
        $this->joinCustomerData();

        return $this;
    }
}
