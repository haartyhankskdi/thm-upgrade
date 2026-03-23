<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Microsoft Consent Mode
 */

namespace Amasty\MicrosoftConsentMode\Setup\SampleData\Installer\GdprCookie;

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
        'Microsoft' => [
            'Cookies' => [
                'MUID' => [
                    'Description' => "Identifies unique users for ad targeting and user profiling.",
                    'Provider' => 'Microsoft',
                    'Type' => CookieTypes::TYPE_3ST_PARTY
                ],
                '_uetsid' => [
                    'Description' => "Tracks user activity on the site for ad targeting.",
                    'Provider' => 'Microsoft',
                    'Type' => CookieTypes::TYPE_3ST_PARTY
                ],
                '_uetvid' => [
                    'Description' => "Stores a unique user identifier for remarketing and retargeting.",
                    'Provider' => 'Microsoft',
                    'Type' => CookieTypes::TYPE_3ST_PARTY
                ]
            ],
            'Description' => "A set of cookies to collect data that helps to track conversions from Microsoft ads,"
                . " optimize ads, build targeted audiences for future ads and remarket to people who have already"
                . " taken some kind of action on website. Updated every 90 days.",
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
