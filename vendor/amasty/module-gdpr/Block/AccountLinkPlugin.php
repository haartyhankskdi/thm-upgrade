<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Block;

use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\Consent\DataProvider\PrivacySettingsDataProvider;
use Amasty\Gdpr\Model\ConsentLogger;
use Magento\Customer\Block\Account\SortLinkInterface as M22LinkClass;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Html\Link\Current as M21LinkClass;

class AccountLinkPlugin
{
    public const SORT_ORDER = 175;
    public const INSERT_AFTER = 'customer-account-navigation-account-edit-link';
    public const LINK_BLOCK_NAME = 'customer-account-amasty-gdpr-settings';
    public const LINK_BLOCK_ALIAS = 'amasty-gdpr-link';

    /**
     * Cache for consent opting section check
     * @var bool|null
     */
    private $isConsentOpting = null;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var PrivacySettingsDataProvider
     */
    private $privacySettingsDataProvider;

    public function __construct(
        Config $configProvider,
        Session $customerSession,
        PrivacySettingsDataProvider $privacySettingsDataProvider
    ) {
        $this->configProvider = $configProvider;
        $this->customerSession = $customerSession;
        $this->privacySettingsDataProvider = $privacySettingsDataProvider;
    }

    /**
     * Insert menu item depending on Magento version
     *
     * @param \Magento\Framework\View\Element\Html\Links|\Magento\Customer\Block\Account\Navigation $subject
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeGetLinks($subject)
    {
        if ($this->isConsentOpting === null) {
            $this->isConsentOpting = !empty($this->privacySettingsDataProvider->getData(
                ConsentLogger::FROM_PRIVACY_SETTINGS
            ));
        }
        if ($subject->getNameInLayout() != 'customer_account_navigation'
            || !$this->configProvider->isModuleEnabled()
            || !$this->customerSession->isLoggedIn()
            || (!$this->configProvider->isAnySectionVisible()
                && !($this->configProvider->isAllowed(Config::CONSENT_OPTING)
                //because consent opting is dynamic section need to check it
                && $this->isConsentOpting)
                && !$this->configProvider->isDisplayDpoInfo()
            )
        ) {
            return;
        }

        $linkClass = interface_exists(M22LinkClass::class) ? M22LinkClass::class : M21LinkClass::class;

        if (!$subject->getLayout()->hasElement(self::LINK_BLOCK_NAME)) {
            $subject->getLayout()->createBlock(
                $linkClass,
                self::LINK_BLOCK_NAME,
                [
                    'data' => [
                        'path' => 'gdpr/customer/settings',
                        'label' => $this->configProvider->getPrivacySettingsTabName(),
                        'sortOrder' => self::SORT_ORDER
                    ]
                ]
            );
        }

        if (!$subject->getChildBlock(self::LINK_BLOCK_ALIAS)) {
            $subject->insert(
                $subject->getLayout()->getBlock(self::LINK_BLOCK_NAME),
                self::INSERT_AFTER,
                true,
                self::LINK_BLOCK_ALIAS
            );
        }
    }
}
