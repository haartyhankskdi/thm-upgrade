<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Block;

use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\Consent\DataProvider\PrivacySettingsDataProviderFactory;
use Amasty\Gdpr\Model\ConsentLogger;
use Magento\Framework\Data\Form\FormKey as FormKey;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class Settings extends Template
{
    public const DOWNLOAD_DATA_BLOCK_SHORT_NAME = 'download';
    public const ANONYMISE_DATA_BLOCK_SHORT_NAME = 'anonymise';
    public const DELETE_ACCOUT_BLOCK_SHORT_NAME = 'delete';
    public const CONSENT_OPTING_BLOCK_SHORT_NAME = 'consent_opting';
    public const VISIBLE_BLOCK_LAYOUT_VARIABLE_NAME = 'visible_block';
    public const NEED_PASSWORD_LAYOUT_VARIABLE_NAME = 'need_password';
    public const IS_ORDER_LAYOUT_VARIABLE_NAME = 'is_order';
    public const DISPLAY_DPO_INFO_BLOCK_SHORT_NAME = 'dpo_info';
    public const POLICIES_TEXT_NAME = 'policies_text';

    /**
     * @var string
     */
    protected $_template = 'settings.phtml';

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var PrivacySettingsDataProviderFactory
     */
    private $privacySettingsDataProviderFactory;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        FormKey $formKey,
        Config $configProvider,
        Manager $moduleManager,
        PrivacySettingsDataProviderFactory $privacySettingsDataProviderFactory,
        array $data = []
    ) {
        parent::__construct($context);
        $this->formKey = $formKey;
        $this->registry = $registry;
        $this->configProvider = $configProvider;
        $this->moduleManager = $moduleManager;
        $this->privacySettingsDataProviderFactory = $privacySettingsDataProviderFactory;
    }

    /**
     * @return array
     */
    public function getPrivacySettings()
    {
        return $this->getAvailableBlocks();
    }

    /**
     * @return array
     */
    private function getPrivacyBlocks()
    {
        $result = [];

        if ($this->configProvider->isModuleEnabled()) {
            if ($this->configProvider->isAllowed(Config::POLICIES_TEXT_SECTION)) {
                $result[self::POLICIES_TEXT_NAME] = [
                    'title' => __('Policies text'),
                    'cssModifier' => '-policy',
                    'policiesText' => true,
                    'actionCode' => 'policies_text',
                    'isCookieEnabled' => $this->moduleManager->isEnabled('Amasty_GdprCookie')
                ];
            }

            if ($this->configProvider->isAllowed(Config::DOWNLOAD) && $this->isVisible()) {
                $downloadContent = $this->isOrder()
                    ? __(
                        'Here you can download a copy of your personal '
                        . 'data which we store for your order in CSV format.'
                    )
                    : __(
                        'Here you can download a copy of your personal '
                        . 'data which we store for your account in CSV format.'
                    );
                $result[self::DOWNLOAD_DATA_BLOCK_SHORT_NAME] = [
                    'title' => __('Download personal data'),
                    'cssModifier' => '-download',
                    'content' => $downloadContent,
                    'hasCheckbox' => true,
                    'checkboxText' => __('Merge into one file'),
                    'checkboxName' => Config::DOWNLOAD_MERGE_INTO_ONE_FILE,
                    'hidePassword' => false,
                    'needPassword' => $this->isNeedPassword(),
                    'submitText' => __('Download'),
                    'action' => $this->getUrl('gdpr/customer/downloadCsv'),
                    'actionCode' => Config::DOWNLOAD,
                ];
            }

            if ($this->configProvider->isAllowed(Config::ANONYMIZE) && $this->isVisible()) {
                $result[self::ANONYMISE_DATA_BLOCK_SHORT_NAME] = [
                    'title' => __('Anonymise personal data'),
                    'cssModifier' => '-anonymise',
                    'content' => __(
                        'By clicking ‘Agree and Proceed’ you agree that your personal data '
                        . 'will be replaced with non-personal anonymous information. When you receive '
                        . 'a new login email, your email address and all other personal data will be '
                        . 'deleted from the website.'
                    ),
                    'hasCheckbox' => false,
                    'checkboxText' => __('I agree and I want to proceed'),
                    'hidePassword' => false,
                    'checkboxDataValidate' => '{required:true}',
                    'needPassword' => $this->isNeedPassword(),
                    'submitText' => __('Agree and Proceed'),
                    'action' => $this->getUrl('gdpr/customer/anonymise'),
                    'actionCode' => Config::ANONYMIZE,
                ];
            }

            if ($this->configProvider->isAllowed(Config::DELETE)) {
                $result[self::DELETE_ACCOUT_BLOCK_SHORT_NAME] = [
                    'title' => __('Delete account'),
                    'cssModifier' => '-delete',
                    'content' => __(
                        'By clicking ‘Agree and Submit request‘ button you agree that all your orders, '
                        . 'personal data and other information associated with your account or purchases will be '
                        . 'lost. You will not be able to regain access to your account after we approve your '
                        . 'deletion request.'
                    ),
                    'checked' => true,
                    'hasCheckbox' => false,
                    'checkboxText' => __('I understand and I want to delete my account'),
                    'hidePassword' => false,
                    'checkboxDataValidate' => '{required:true}',
                    'needPassword' => $this->isNeedPassword(),
                    'submitText' => __('Agree and Submit request'),
                    'action' => $this->getUrl('gdpr/customer/addDeleteRequest'),
                    'actionCode' => Config::DELETE,
                ];
            }
            $privacySettingsProvider = $this->privacySettingsDataProviderFactory->create();

            if ($this->configProvider->isAllowed(Config::CONSENT_OPTING)
                && count($privacySettingsProvider->getData(ConsentLogger::FROM_PRIVACY_SETTINGS)) > 0
            ) {
                $checkboxBlock = $this->getLayout()->createBlock(
                    AccountCheckbox::class,
                    'opting_consents_block',
                    [
                        'dataProvider' => $privacySettingsProvider,
                        'scope' => ConsentLogger::FROM_PRIVACY_SETTINGS
                    ]
                );
                $result[self::CONSENT_OPTING_BLOCK_SHORT_NAME] = [
                    'title' => __('Given Consent'),
                    'cssModifier' => '-consents',
                    'content' => __('Here you can opt in or opt out from consents that were previously given.'),
                    'hasCheckbox' => false,
                    'checkboxText' => '',
                    'additionalBlock' => $checkboxBlock,
                    'hidePassword' => true,
                    'needPassword' => false,
                    'submitText' => __('Save'),
                    'action' => $this->getUrl('gdpr/customer/saveConsentChanges'),
                    'actionCode' => Config::GIVEN_CONSENTS,
                ];
            }

            if ($this->configProvider->isDisplayDpoInfo()) {
                $sendEmailContent = $this->configProvider->getDpoEmail()
                    ? ' <a class="action primary amgdpr-mail" href="mailto:'
                    . $this->configProvider->getDpoEmail() . '">' . __('Send Email') . '</a>'
                    : '';
                $result[self::DISPLAY_DPO_INFO_BLOCK_SHORT_NAME] = [
                    'title' => $this->configProvider->getDpoSectionName(),
                    'cssModifier' => '-dpoinfo',
                    'actionCode' => 'dpo_info',
                    'content' => $this->configProvider->getDpoInfo() . $sendEmailContent
                ];
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getAvailableBlocks()
    {
        $result = [];
        $allBlocks = $this->getPrivacyBlocks();
        $visibleBlocks = $this->getData(self::VISIBLE_BLOCK_LAYOUT_VARIABLE_NAME)
            ? explode(',', $this->getData(self::VISIBLE_BLOCK_LAYOUT_VARIABLE_NAME)) : [];

        if (!$visibleBlocks) {
            return $allBlocks;
        }

        foreach ($visibleBlocks as $blockName) {
            if (array_key_exists($blockName, $allBlocks)) {
                $result[$blockName] = $allBlocks[$blockName];
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isNeedPassword()
    {
        return $this->getData(self::NEED_PASSWORD_LAYOUT_VARIABLE_NAME);
    }

    /**
     * @return bool
     */
    private function isOrder()
    {
        return (bool)$this->getData(self::IS_ORDER_LAYOUT_VARIABLE_NAME);
    }

    /**
     * @return bool
     */
    private function isCustomerGuest()
    {
        $currentOrder = $this->registry->registry('current_order');

        return $currentOrder ? $currentOrder->getCustomerIsGuest() : false;
    }

    /**
     * @return bool
     */
    private function isVisible()
    {
        return ($this->isOrder() && $this->isCustomerGuest()) || !$this->isOrder();
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Remove Html tags
     *
     * @param $content string|string[]
     * @param $tagsToRemove string[]
     * @return string|string[]
     */
    public function stripHtmlTags($content, array $tagsToRemove)
    {
        $tags = [];
        foreach ($tagsToRemove as $tag) {
            array_push($tags, sprintf('<%s>', $tag), sprintf('</%s>', $tag));
        }

        return str_ireplace($tags, '', $content);
    }
}
