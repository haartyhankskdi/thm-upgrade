<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Setup\Patch\Data;

use Amasty\GdprCookie\Model\CookieFactory;
use Amasty\GdprCookie\Model\CookieGroupFactory;
use Amasty\GdprCookie\Model\OptionSource\Cookie\Types as CookieTypes;
use Amasty\GdprCookie\Model\Repository\CookieGroupsRepository;
use Amasty\GdprCookie\Model\Repository\CookieRepository;
use Amasty\GdprCookie\Model\ResourceModel\Cookie as CookieResource;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class InstallCookieData implements DataPatchInterface
{
    /**
     * @var array[]
     */
    private $cookiesByGroups = [
        'Essential' => [
            'Cookies' => [
                'PHPSESSID' => [
                    'Description' => "To store the logged in user's username and a 128bit encrypted key."
                        . "This information is required to allow a user to stay logged in to a web site"
                        . "without needing to submit their username and password for each page visited."
                        . " Without this cookie, a user is unabled to proceed to areas of the web site"
                        . " that require authenticated access.",
                    'Provider' => 'Magento',
                    'Type' => CookieTypes::TYPE_1ST_PARTY
                ],
                'private_content_version' => [
                    'Description' => "Appends a random, unique number and time to pages with customer content"
                        . " to prevent them from being cached on the server.",
                    'Provider' => 'Magento',
                    'Type' => CookieTypes::TYPE_1ST_PARTY
                ],
                'persistent_shopping_cart' => [
                    'Description' => "Stores the key (ID) of persistent cart to make it possible to"
                        . " restore the cart for an anonymous shopper.",
                    'Provider' => 'Magento',
                    'Type' => CookieTypes::TYPE_1ST_PARTY
                ],
                'form_key' => [
                    'Description' => "A security measure that appends a random string to all form submissions"
                        . " to protect the data from Cross-Site Request Forgery (CSRF).",
                    'Provider' => 'Magento',
                    'Type' => CookieTypes::TYPE_1ST_PARTY
                ],
                'store' => [
                    'Description' => "Tracks the specific store view / locale selected by the shopper.",
                    'Provider' => 'Magento',
                    'Type' => CookieTypes::TYPE_1ST_PARTY
                ],
                'login_redirect' => [
                    'Description' => "Preserves the destination page the customer was navigating"
                        . " to before being directed to log in.",
                    'Provider' => 'Magento',
                    'Type' => CookieTypes::TYPE_1ST_PARTY
                ],
                'mage-messages' => [
                    'Description' => "Tracks error messages and other notifications that are shown to the user,"
                        . " such as the cookie consent message, and various error messages, The message is"
                        . " deleted from the cookie after it is shown to the shopper.",
                    'Provider' => 'Magento',
                    'Type' => CookieTypes::TYPE_1ST_PARTY
                ],
                'mage-cache-storage' => [
                    'Description' => "Local storage of visitor-specific content that enables e-commerce functions.",
                    'Provider' => 'Magento',
                    'Type' => CookieTypes::TYPE_1ST_PARTY
                ],
                'mage-cache-storage-section-invalidation' => [
                    'Description' => "Forces local storage of specific content sections"
                        . " that should be invalidated.",
                    'Provider' => 'Magento',
                    'Type' => CookieTypes::TYPE_1ST_PARTY
                ],
                'mage-cache-sessid' => [
                    'Description' => "The value of this cookie triggers the cleanup of local cache storage.",
                    'Provider' => 'Magento',
                    'Type' => CookieTypes::TYPE_1ST_PARTY
                ],
                'product_data_storage' => [
                    'Description' => "Stores configuration for product data related to Recently Viewed"
                        . " / Compared Products.",
                    'Provider' => 'Magento',
                    'Type' => CookieTypes::TYPE_1ST_PARTY
                ],
                'user_allowed_save_cookie' => [
                    'Description' => "Indicates if the shopper allows cookies to be saved.",
                    'Provider' => 'Magento',
                    'Type' => CookieTypes::TYPE_1ST_PARTY
                ],
                'mage-translation-storage' => [
                    'Description' => "Stores translated content when requested by the shopper.",
                    'Provider' => 'Magento',
                    'Type' => CookieTypes::TYPE_1ST_PARTY
                ],
                'mage-translation-file-version' => [
                    'Description' => "Stores the file version of translated content.",
                    'Provider' => 'Magento',
                    'Type' => CookieTypes::TYPE_1ST_PARTY
                ],
                'section_data_ids' => [
                    'Description' => "Stores customer-specific information related to shopper-initiated actions"
                        . " such as display wish list, checkout information, etc.",
                    'Provider' => 'Magento',
                    'Type' => CookieTypes::TYPE_1ST_PARTY
                ]
            ],
            'Description' => "Necessary cookies enable core functionality of the website. Without these"
                . " cookies the website can not function properly. They help to make a website usable"
                . " by enabling basic functionality.",
            'Essential' => true,
            'Enabled' => true
        ],
        'Marketing' => [
            'Cookies' => [
                'recently_viewed_product' => [
                    'Description' => "Stores product IDs of recently viewed products for easy navigation.",
                    'Provider' => 'Magento',
                    'Type' => CookieTypes::TYPE_1ST_PARTY
                ],
                'recently_viewed_product_previous' => [
                    'Description' => "Stores product IDs of recently previously viewed"
                        . " products for easy navigation.",
                    'Provider' => 'Magento',
                    'Type' => CookieTypes::TYPE_1ST_PARTY
                ],
                'recently_compared_product' => [
                    'Description' => "Stores product IDs of recently compared products.",
                    'Provider' => 'Magento',
                    'Type' => CookieTypes::TYPE_1ST_PARTY
                ],
                'recently_compared_product_previous' => [
                    'Description' => "Stores product IDs of previously compared"
                        . " products for easy navigation.",
                    'Provider' => 'Magento',
                    'Type' => CookieTypes::TYPE_1ST_PARTY
                ]
            ],
            'Description' => "Marketing cookies are used to track and collect visitors actions"
                . " on the website. Cookies store user data and behaviour information, which"
                . " allows advertising services to target more audience groups. Also more customized"
                . " user experience can be provided according to collected information.",
            'Essential' => false,
            'Enabled' => true
        ],
        'Analytics' => [
            'Cookies' => [
                '_ga' => [
                    'Description' => "Used to distinguish users.",
                    'Provider' => 'Google Analytics',
                    'Type' => CookieTypes::TYPE_3ST_PARTY
                ],
                '_gid' => [
                    'Description' => "Used to distinguish users.",
                    'Provider' => 'Google Analytics',
                    'Type' => CookieTypes::TYPE_3ST_PARTY
                ],
                '_gat' => [
                    'Description' => "Used to throttle request rate.",
                    'Provider' => 'Google Analytics',
                    'Type' => CookieTypes::TYPE_3ST_PARTY
                ]
            ],
            'Description' => "A set of cookies to collect information and report about website usage statistics"
                . " without personally identifying individual visitors to Google.",
            'Essential' => false,
            'Enabled' => true
        ]
    ];

    /**
     * @var CookieGroupFactory
     */
    private $cookieGroupFactory;

    /**
     * @var CookieGroupsRepository
     */
    private $cookieGroupsRepository;

    /**
     * @var CookieFactory
     */
    private $cookieFactory;

    /**
     * @var CookieRepository
     */
    private $cookieRepository;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    public function __construct(
        CookieGroupFactory $cookieGroupFactory,
        CookieGroupsRepository $cookieGroupsRepository,
        CookieFactory $cookieFactory,
        CookieRepository $cookieRepository,
        ModuleDataSetupInterface $moduleDataSetup,
        ResourceInterface $moduleResource
    ) {
        $this->cookieGroupFactory = $cookieGroupFactory;
        $this->cookieGroupsRepository = $cookieGroupsRepository;
        $this->cookieFactory = $cookieFactory;
        $this->cookieRepository = $cookieRepository;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->moduleResource = $moduleResource;
    }

    public function apply(): InstallCookieData
    {
        $setupDataVersion = (string)$this->moduleResource->getDataVersion('Amasty_GdprCookie');
        if (!$setupDataVersion || version_compare($setupDataVersion, '2.2.0', '<')) {
            foreach ($this->cookiesByGroups as $groupName => $groupData) {
                if (!$setupDataVersion) {
                    $cookieGroup = $this->cookieGroupFactory->create();
                    $cookieGroup->setName($groupName);
                    $cookieGroup->setDescription($groupData['Description']);
                    $cookieGroup->setIsEnabled($groupData['Enabled']);
                    $cookieGroup->setIsEssential($groupData['Essential']);

                    $this->cookieGroupsRepository->save($cookieGroup);
                    $groupId = $cookieGroup->getId();
                }

                foreach ($groupData['Cookies'] as $name => $cookieData) {
                    if (!$setupDataVersion) {
                        try {
                            $cookie = $this->cookieRepository->getByName($name);
                        } catch (NoSuchEntityException $e) {
                            $cookie = $this->cookieFactory->create();
                            $cookie->setName($name);
                            $cookie->setDescription($cookieData['Description']);
                        }
                    } else {
                        try {
                            $cookie = $this->cookieRepository->getByName($name);
                        } catch (NoSuchEntityException $e) {
                            continue;
                        }
                    }

                    $cookie->setProvider($cookieData['Provider']);
                    $cookie->setType($cookieData['Type']);
                    $this->cookieRepository->save($cookie);

                    if (!$setupDataVersion) {
                        $this->moduleDataSetup->getConnection()->update(
                            $this->moduleDataSetup->getTable(CookieResource::TABLE_NAME),
                            ['group_id' => $groupId],
                            ['id = ?' => $cookie->getId()]
                        );
                    }
                }
            }
        }

        return $this;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
