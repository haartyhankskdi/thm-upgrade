<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Cookie Clarity Consent Mode
 */

namespace Amasty\ClarityConsentMode\Setup\SampleData\Installer\GdprCookie;

use Amasty\GdprCookie\Model\CookieFactory;
use Amasty\GdprCookie\Model\CookieGroupFactory;
use Amasty\GdprCookie\Model\OptionSource\Cookie\Types as CookieTypes;
use Amasty\GdprCookie\Model\Repository\CookieGroupsRepository;
use Amasty\GdprCookie\Model\Repository\CookieRepository;
use Amasty\GdprCookie\Model\ResourceModel\Cookie as CookieResource;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SampleData\InstallerInterface;

class CookiesAndGroups implements InstallerInterface
{
    /**
     * @var array[]
     */
    private $cookiesByGroups = [
        'Clarity' => [
            'Cookies' => [
                '_clck' => [
                    'Description' => "Persists the Clarity User ID and preferences, unique to that site"
                        . " is attributed to the same user ID.",
                    'Provider' => 'Clarity',
                    'Type' => CookieTypes::TYPE_3ST_PARTY
                ],
                '_clsk' => [
                    'Description' => "Connects multiple page views by a user into a single Clarity session recording.",
                    'Provider' => 'Clarity',
                    'Type' => CookieTypes::TYPE_3ST_PARTY
                ],
                'CLID' => [
                    'Description' => "Identifies the first-time Clarity saw this user on any site using Clarity.",
                    'Provider' => 'Clarity',
                    'Type' => CookieTypes::TYPE_3ST_PARTY
                ],
                'ANONCHK' => [
                    'Description' => "Indicates whether MUID is transferred to ANID, a cookie used for advertising."
                        ." Clarity doesn't use ANID and so this is always set to 0.",
                    'Provider' => 'Clarity',
                    'Type' => CookieTypes::TYPE_3ST_PARTY
                ],
                'MR' => [
                    'Description' => "Indicates whether to refresh MUID.",
                    'Provider' => 'Clarity',
                    'Type' => CookieTypes::TYPE_3ST_PARTY
                ],
                'MUID (clarity)' => [
                    'Description' => "Identifies unique web browsers visiting Microsoft sites."
                        ." These cookies are used for advertising, site analytics, and other operational purposes.",
                    'Provider' => 'Clarity',
                    'Type' => CookieTypes::TYPE_3ST_PARTY
                ],
                'SM' => [
                    'Description' => "Used in synchronizing the MUID across Microsoft domains.",
                    'Provider' => 'Clarity',
                    'Type' => CookieTypes::TYPE_3ST_PARTY
                ]
            ],
            'Description' => "A set of cookies used to collect data about how visitors interact with the website, "
                . "such as clicks, scrolls, and page views. This information helps analyze user behavior, improve site "
                . "usability, and measure content performance.",
            'Essential' => false,
            'Enabled' => true
        ]
    ];

    public function __construct(
        private readonly CookieGroupFactory $cookieGroupFactory,
        private readonly CookieGroupsRepository $cookieGroupsRepository,
        private readonly CookieFactory $cookieFactory,
        private readonly CookieRepository $cookieRepository,
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    public function install(): void
    {
        $this->moduleDataSetup->startSetup();

        foreach ($this->cookiesByGroups as $groupName => $groupData) {
            $cookieGroup = $this->cookieGroupFactory->create();
            $cookieGroup->setName($groupName);
            $cookieGroup->setDescription($groupData['Description']);
            $cookieGroup->setIsEnabled($groupData['Enabled']);
            $cookieGroup->setIsEssential($groupData['Essential']);
            $this->cookieGroupsRepository->save($cookieGroup);

            $groupId = $cookieGroup->getId();
            $createdCookieIds = [];
            foreach ($groupData['Cookies'] as $name => $cookieData) {
                $cookie = $this->cookieFactory->create();
                $cookie->setName($name);
                $cookie->setDescription($cookieData['Description']);
                $cookie->setProvider($cookieData['Provider']);
                $cookie->setType($cookieData['Type']);
                $this->cookieRepository->save($cookie);
                $createdCookieIds[] = $cookie->getId();
            }
            $this->moduleDataSetup->getConnection()->update(
                $this->moduleDataSetup->getTable(CookieResource::TABLE_NAME),
                ['group_id' => $groupId],
                ['id IN (?)' => $createdCookieIds]
            );

            $this->moduleDataSetup->endSetup();
        }
    }
}
