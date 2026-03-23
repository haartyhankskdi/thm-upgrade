<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Test\Unit\Model\Consent\DataProvider;

use Amasty\Gdpr\Api\ConsentRepositoryInterface;
use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Model\Consent\Consent;
use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStore;
use Amasty\Gdpr\Model\Consent\DataProvider\ConsentPolicyContentResolver;
use Amasty\Gdpr\Model\Consent\Repository;
use Amasty\Gdpr\Model\Policy;
use Amasty\Gdpr\Model\PolicyRepository;
use Amasty\Gdpr\Model\Source\ConsentLinkType;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageRepository;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers ConsentPolicyContentResolver
 */
class ConsentPolicyContentResolverTest extends \PHPUnit\Framework\TestCase
{
    public const TEST_CONSENT_ID = 1;
    public const NOT_EXISTING_CMS_PAGE_ID = 0;
    public const EXISTING_CMS_PAGE_ID = 1;

    /**
     * @var PageRepositoryInterface|MockObject
     */
    private $pageRepositoryMock;

    /**
     * @var PolicyRepositoryInterface|MockObject
     */
    private $policyRepositoryMock;

    /**
     * @var ConsentRepositoryInterface|MockObject
     */
    private $consentRepositoryMock;

    /**
     * @var ConsentPolicyContentResolver
     */
    private $consentPolicyContentResolver;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $storeMock = $this->createPartialMock(Store::class, ['getId']);
        $storeMock->expects($this->any())->method('getId')->willReturn(1);
        $storeManagerMock = $this->createPartialMock(
            \Magento\Store\Model\StoreManager::class,
            ['getStore']
        );
        $storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);
        $this->pageRepositoryMock = $this->createPartialMock(PageRepository::class, ['getById']);
        $this->policyRepositoryMock = $this->createPartialMock(
            PolicyRepository::class,
            ['getCurrentPolicy']
        );
        $this->consentRepositoryMock = $this->createPartialMock(Repository::class, ['getById']);

        $this->consentPolicyContentResolver = $objectManager->getObject(
            ConsentPolicyContentResolver::class,
            [
                'storeManager' => $storeManagerMock,
                'pageRepository' => $this->pageRepositoryMock,
                'policyRepository' => $this->policyRepositoryMock,
                'consentRepository' => $this->consentRepositoryMock
            ]
        );
    }

    /**
     * @param Consent|MockObject $consentMock
     * @param Policy|MockObject $policyMock
     * @param array $expectedPolicyData
     *
     * @covers \Amasty\Gdpr\Model\Consent\DataProvider\ConsentPolicyContentResolver::getConsentPolicyData
     * @dataProvider getConsentPolicyDataWithGeneralPolicyDataProvider
     */
    public function testGetConsentPolicyDataWithGeneralPolicy($consentMock, $policyMock, $expectedPolicyData)
    {
        $this->consentRepositoryMock
            ->expects($this->any())
            ->method('getById')
            ->willReturn($consentMock);

        $this->policyRepositoryMock
            ->expects($this->any())
            ->method('getCurrentPolicy')
            ->willReturn($policyMock);

        $policyData = $this->consentPolicyContentResolver->getConsentPolicyData(self::TEST_CONSENT_ID);
        $this->assertEquals($expectedPolicyData, $policyData);
    }

    public function getConsentPolicyDataWithGeneralPolicyDataProvider()
    {
        return [
            [
                $this->createConfiguredMock(
                    Consent::class,
                    ['getPrivacyLinkType' => ConsentLinkType::PRIVACY_POLICY]
                ),
                $this->createConfiguredMock(
                    Policy::class,
                    ['getContent' => 'General policy text']
                ),
                [
                    'title' => __('Privacy Policy'),
                    'content' => 'General policy text'
                ]
            ],
            [
                $this->createConfiguredMock(
                    Consent::class,
                    ['getPrivacyLinkType' => ConsentLinkType::PRIVACY_POLICY]
                ),
                false,
                [
                    'title' => __('Privacy Policy'),
                    'content' => ''
                ]
            ],
        ];
    }

    /**
     * @param int $cmsPageId
     * @param Page|MockObject $cmsPageMock
     * @param array $expectedPolicyData
     *
     * @covers \Amasty\Gdpr\Model\Consent\DataProvider\ConsentPolicyContentResolver::getConsentPolicyData
     * @dataProvider getConsentPolicyDataWithCmsPageDataProvider
     */
    public function testGetConsentPolicyContentWithCmsPage($cmsPageId, $cmsPageMock, $expectedPolicyData)
    {
        $consentStoreModelMock = $this->createPartialMock(ConsentStore::class, ['getData']);
        $consentStoreModelMock
            ->expects($this->any())
            ->method('getData')
            ->with(ConsentStore::CMS_PAGE_ID)
            ->willReturn($cmsPageId);

        $consentMock = $this->createPartialMock(Consent::class, ['getPrivacyLinkType', 'getStoreModel']);
        $consentMock
            ->expects($this->any())
            ->method('getPrivacyLinkType')
            ->willReturn(ConsentLinkType::CMS_PAGE);
        $consentMock
            ->expects($this->any())
            ->method('getStoreModel')
            ->willReturn($consentStoreModelMock);

        $this->consentRepositoryMock
            ->expects($this->any())
            ->method('getById')
            ->willReturn($consentMock);

        $this->pageRepositoryMock
            ->expects($this->any())
            ->method('getById')
            ->willReturn($cmsPageMock);

        $policyData = $this->consentPolicyContentResolver->getConsentPolicyData(self::TEST_CONSENT_ID);
        $this->assertEquals($expectedPolicyData, $policyData);
    }

    public function getConsentPolicyDataWithCmsPageDataProvider()
    {
        return [
            [
                self::EXISTING_CMS_PAGE_ID,
                $this->createConfiguredMock(
                    Page::class,
                    [
                        'getContent' => 'CMS page policy content',
                        'getTitle' => 'CMS page title'
                    ]
                ),
                [
                    'title' => 'CMS page title',
                    'content' => 'CMS page policy content'
                ]
            ],
            [
                self::EXISTING_CMS_PAGE_ID,
                $this->createConfiguredMock(
                    Page::class,
                    [
                        'getContent' => 'CMS page policy content',
                        'getTitle' => null
                    ]
                ),
                [
                    'title' =>  __('Privacy Policy'),
                    'content' => 'CMS page policy content'
                ]
            ],
            [
                self::NOT_EXISTING_CMS_PAGE_ID,
                null,
                []
            ],
        ];
    }
}
