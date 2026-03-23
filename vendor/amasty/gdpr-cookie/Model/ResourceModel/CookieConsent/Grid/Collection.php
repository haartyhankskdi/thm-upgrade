<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\ResourceModel\CookieConsent\Grid;

use Amasty\GdprCookie\Model\CookieConsent;
use Amasty\GdprCookie\Model\CookieConsent\CookieGroupProcessor;
use Amasty\GdprCookie\Model\ResourceModel\CookieConsent as CookieConsentResource;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroup;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

/**
 * @method CookieConsent[] getItems()
 */
class Collection extends SearchResult
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->_init(Document::class, CookieConsentResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFilterToMap('id', 'main_table.id');

        $connection = $this->getConnection();
        $guest = $connection->quote(__('Guest'));
        $allAllowed = $connection->quote(__('All Allowed'));
        $noneAllowed = $connection->quote(__('None cookies allowed'));
        $unknown = $connection->quote('-');
        $columnStatusText = new \Zend_Db_Expr(
            sprintf(
                'GROUP_CONCAT(%s ORDER BY %s SEPARATOR \', \')',
                $connection->getCaseSql(
                    'main_table.groups_status',
                    [
                        $connection->quote(CookieConsent::GROUPS_STATUS_ALL_ALLOWED) => $allAllowed,
                        $connection->quote(CookieConsent::GROUPS_STATUS_NONE_ALLOWED) => $noneAllowed,
                        $connection->quote(CookieConsent::GROUPS_STATUS_SPECIFIC_GROUP) =>
                            $connection->quoteIdentifier(
                                'cookie_group.name'
                            )
                    ],
                    $unknown
                ),
                $connection->quoteIdentifier('cookie_group.name')
            )
        );

        $this->getSelect()->joinLeft(
            ['customer' => $this->getTable('customer_entity')],
            'customer.entity_id = main_table.customer_id',
            [
                'email' => new \Zend_Db_Expr("IF(main_table.customer_id != 0, email, $unknown)"),
                'name' => new \Zend_Db_Expr(
                    "IF(main_table.customer_id != 0, CONCAT_WS(' ', prefix, "
                    . "firstname, middlename, lastname, suffix), $guest)"
                )
            ]
        );

        $this->getSelect()->joinLeft(
            ['consent_status' => $this->getTable(CookieConsentResource::STATUS_TABLE_NAME)],
            'consent_status.cookie_consents_id = main_table.id'
            . ' AND main_table.groups_status = ' . CookieConsent::GROUPS_STATUS_SPECIFIC_GROUP
            . ' AND consent_status.status = ' . CookieGroupProcessor::CONSENT_STATUS_ACCEPTED,
            []
        )->joinLeft(
            ['cookie_group' => $this->getTable(CookieGroup::TABLE_NAME)],
            'cookie_group.id = consent_status.group_id',
            [
                'status_text' => $columnStatusText
            ]
        )->group('main_table.id');

        return $this;
    }
}
