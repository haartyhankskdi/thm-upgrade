<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\ResourceModel;

use Amasty\GdprCookie\Api\CookieManagementInterface;
use Amasty\GdprCookie\Model\CookieConsent\CookieGroupProcessor;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class CookieConsent extends AbstractDb
{
    public const TABLE_NAME = 'amasty_gdprcookie_cookie_consents';
    public const STATUS_TABLE_NAME = 'amasty_gdprcookie_cookie_consent_status';

    /**
     * @var CookieGroupProcessor
     */
    private $cookieGroupProcessor;

    /**
     * @var CookieManagementInterface
     */
    private $cookieManagement;

    public function __construct(
        Context $context,
        CookieGroupProcessor $cookieGroupProcessor,
        CookieManagementInterface $cookieManagement,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->cookieGroupProcessor = $cookieGroupProcessor;
        $this->cookieManagement = $cookieManagement;
    }
    public function _construct()
    {
        $this->_init(self::TABLE_NAME, 'id');
    }

    protected function _afterSave(AbstractModel $object)
    {
        parent::_afterSave($object);

        $consentStatusTable = $this->getTable(self::STATUS_TABLE_NAME);
        $dataToInsert = [];
        $groups = $this->cookieManagement->getAvailableGroups((int)$object->getWebsite());

        if ($groups) {
            foreach ($groups as $group) {
                $dataToInsert[] = [
                    'cookie_consents_id' => $object->getId(),
                    'group_id' => $group->getId(),
                    'status' => $this->cookieGroupProcessor->getConsentStatus(
                        $object->getAllowedGroupIds(),
                        $group
                    )
                ];
            }

            $this->getConnection()->insertOnDuplicate($consentStatusTable, $dataToInsert);
        }

        return $this;
    }
}
