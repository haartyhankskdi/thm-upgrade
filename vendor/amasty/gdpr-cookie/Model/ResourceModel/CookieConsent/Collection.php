<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\ResourceModel\CookieConsent;

use Amasty\GdprCookie\Model\CookieConsent;
use Amasty\GdprCookie\Model\ResourceModel\CookieConsent as CookieConsentResource;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroup;
use Amasty\GdprCookie\Setup\Operation\CreateCookieGroupsTable;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method CookieConsent[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->_init(CookieConsent::class, CookieConsentResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function joinConsentStatusChartData()
    {
        if (!$this->getFlag('consent_status_table_joined')) {
            $this->getSelect()->reset(Select::COLUMNS)
                ->joinInner(
                    ['consent_status' => $this->getTable(CookieConsentResource::STATUS_TABLE_NAME)],
                    'consent_status.cookie_consents_id = main_table.id',
                    []
                )->joinLeft(
                    ['cookie_group' => $this->getTable(CookieGroup::TABLE_NAME)],
                    'cookie_group.id = consent_status.group_id',
                    [
                        'groupName' => 'cookie_group.name',
                        'accepted' => new \Zend_Db_Expr('SUM(consent_status.status)'),
                        'rejected' => new \Zend_Db_Expr('SUM(IF(consent_status.status = 0, 1, 0))')
                    ]
                )->group('consent_status.group_id');
            $this->setFlag('consent_status_table_joined', true);
        }

        return $this;
    }
}
