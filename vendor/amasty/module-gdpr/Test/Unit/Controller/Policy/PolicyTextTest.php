<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Test\Unit\Controller\Policy;

use Amasty\Gdpr\Controller\Policy\PolicyText;
use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\Consent\DataProvider\ConsentPolicyContentResolver;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\App\Response\Http as HttpResponse;

/**
 * @covers PolicyText
 */
class PolicyTextTest extends \PHPUnit\Framework\TestCase
{
    public const TEST_POLICY_DATA = [
        ConsentPolicyContentResolver::DATA_TITLE => 'Policy Title',
        ConsentPolicyContentResolver::DATA_CONTENT => 'Policy Content'
    ];

    /**
     * @var PolicyText
     */
    private $controller;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\Filter\Template|MockObject
     */
    private $templateFilterMock;

    /**
     * @var HttpResponse|MockObject
     */
    private $responseMock;

    /**
     * @var Config|MockObject
     */
    private $configProviderMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var Json|MockObject
     */
    private $jsonResponseMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->configProviderMock = $this->createPartialMock(Config::class, ['isModuleEnabled']);
        $this->templateFilterMock =  $this->createPartialMock(\Magento\Framework\Filter\Template::class, ['filter']);
        $this->responseMock = $this->createPartialMock(HttpResponse::class, ['setPublicHeaders']);
        $filterProvider = $this->createConfiguredMock(
            FilterProvider::class,
            ['getPageFilter' => $this->templateFilterMock]
        );
        $policyContentResolverMock = $this->createConfiguredMock(
            ConsentPolicyContentResolver::class,
            [
                'getConsentPolicyData' => self::TEST_POLICY_DATA,
                'getGeneralPolicyData' => self::TEST_POLICY_DATA
            ]
        );
        $this->resultFactoryMock = $this->createPartialMock(ResultFactory::class, ['create']);
        $this->jsonResponseMock = $this->createPartialMock(Json::class, ['setData']);
        $this->requestMock = $this->createPartialMock(Http::class, ['getParam']);
        $actionContext = $this->createConfiguredMock(
            \Magento\Framework\App\Action\Context::class,
            [
                'getResponse' => $this->responseMock,
                'getRequest' => $this->requestMock,
                'getResultFactory' => $this->resultFactoryMock
            ]
        );
        $this->controller = $objectManager->getObject(
            PolicyText::class,
            [
                'context' => $actionContext,
                'configProvider' => $this->configProviderMock,
                'filterProvider' => $filterProvider,
                'policyContentResolver' => $policyContentResolverMock
            ]
        );
    }

    /**
     * @param int $consentId
     * @param bool $moduleEnabled
     * @param array $expectedPolicyData
     *
     * @dataProvider executeDataProvider
     */
    public function testExecute($consentId, $moduleEnabled, $expectedPolicyData)
    {
        $this->requestMock
            ->expects($this->any())
            ->method('getParam')
            ->with('consent_id')
            ->willReturn($consentId);

        $this->configProviderMock
            ->expects($this->any())
            ->method('isModuleEnabled')
            ->willReturn($moduleEnabled);

        $this->templateFilterMock
            ->expects($this->any())
            ->method('filter')
            ->willReturnArgument(0);

        $this->resultFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->jsonResponseMock);

        $this->jsonResponseMock
            ->expects($this->any())
            ->method('setData')
            ->with($expectedPolicyData)
            ->willReturn($this->jsonResponseMock);

        $this->assertInstanceOf(Json::class, $this->controller->execute());
    }

    public function executeDataProvider()
    {
        return [
            [1, true, self::TEST_POLICY_DATA],
            [1, false, []],
            [0, true, self::TEST_POLICY_DATA],
            [0, false, []],
        ];
    }
}
