<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Test\Unit\Model;

use Amasty\Gdpr\Model\Visitor;
use Amasty\Geoip\Model\Geolocation;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

/**
 * @covers \Amasty\Gdpr\Model\Visitor
 */
class VisitorTest extends \PHPUnit\Framework\TestCase
{
    public const IP_ADDRESS = '150.10.0.1';

    /**
     * @var Visitor
     */
    private $visitor;

    /**
     * @var CustomerSession|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerSessionMock;

    /**
     * @var CheckoutSession|\PHPUnit\Framework\MockObject\MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var Geolocation|\PHPUnit\Framework\MockObject\MockObject
     */
    private $geolocationMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $this->checkoutSessionMock = $this->createMock(CheckoutSession::class);
        $this->geolocationMock = $this->createMock(Geolocation::class);
        $remoteAddressMock = $this->createConfiguredMock(
            RemoteAddress::class,
            ['getRemoteAddress' => self::IP_ADDRESS]
        );

        $this->visitor = $objectManager->getObject(
            Visitor::class,
            [
                'customerSession' => $this->customerSessionMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'geolocation' => $this->geolocationMock,
                'remoteAddress' => $remoteAddressMock
            ]
        );
    }

    public function testGetCountryCodeCache()
    {
        $reflection = new \ReflectionClass(Visitor::class);
        $property = $reflection->getProperty('cacheCountryCode');
        $property->setAccessible(true);
        $property->setValue($this->visitor, 'cached');

        $this->assertEquals('cached', $this->visitor->getCountryCode());
    }

    /**
     * @param Quote|\PHPUnit\Framework\MockObject\MockObject $quoteMock
     * @param Customer|\PHPUnit\Framework\MockObject\MockObject $customerMock
     * @param array $geolocationLocate
     * @param string|bool $expectedResult
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @dataProvider getCountryCodeDataProvider
     */
    public function testGetCountryCode($quoteMock, $customerMock, $geolocationLocate, $expectedResult)
    {
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $this->customerSessionMock->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customerMock);

        $this->geolocationMock->expects($this->any())
            ->method('locate')
            ->willReturn($geolocationLocate);

        $this->assertEquals($expectedResult, $this->visitor->getCountryCode());
    }

    public function getCountryCodeDataProvider(): array
    {
        return [
            [
                $this->createConfiguredMock(
                    Quote::class,
                    [
                        'getShippingAddress' => $this->createConfiguredMock(
                            Address::class,
                            [
                                'getCountry' => null
                            ]
                        ),
                        'getBillingAddress' => $this->createConfiguredMock(
                            Address::class,
                            [
                                'getCountry' => null
                            ]
                        )
                    ]
                ),
                $this->createConfiguredMock(
                    Customer::class,
                    [
                        'getPrimaryBillingAddress' => $this->createConfiguredMock(
                            Address::class,
                            [
                                'getCountry' => null
                            ]
                        )
                    ]
                ),
                [],
                false
            ],
            [
                $this->createConfiguredMock(
                    Quote::class,
                    [
                        'getShippingAddress' => $this->createConfiguredMock(
                            Address::class,
                            [
                                'getCountry' => 'US'
                            ]
                        ),
                        'getBillingAddress' => $this->createConfiguredMock(
                            Address::class,
                            [
                                'getCountry' => null
                            ]
                        )
                    ]
                ),
                $this->createConfiguredMock(
                    Customer::class,
                    [
                        'getPrimaryBillingAddress' => $this->createConfiguredMock(
                            Address::class,
                            [
                                'getCountry' => null
                            ]
                        )
                    ]
                ),
                [],
                'US'
            ],
            [
                $this->createConfiguredMock(
                    Quote::class,
                    [
                        'getShippingAddress' => $this->createConfiguredMock(
                            Address::class,
                            [
                                'getCountry' => null
                            ]
                        ),
                        'getBillingAddress' => $this->createConfiguredMock(
                            Address::class,
                            [
                                'getCountry' => 'UK'
                            ]
                        )
                    ]
                ),
                $this->createConfiguredMock(
                    Customer::class,
                    [
                        'getPrimaryBillingAddress' => $this->createConfiguredMock(
                            Address::class,
                            [
                                'getCountry' => null
                            ]
                        )
                    ]
                ),
                [],
                'UK'
            ],
            [
                $this->createConfiguredMock(
                    Quote::class,
                    [
                        'getShippingAddress' => $this->createConfiguredMock(
                            Address::class,
                            [
                                'getCountry' => null
                            ]
                        ),
                        'getBillingAddress' => $this->createConfiguredMock(
                            Address::class,
                            [
                                'getCountry' => null
                            ]
                        )
                    ]
                ),
                $this->createConfiguredMock(
                    Customer::class,
                    [
                        'getPrimaryBillingAddress' => $this->createConfiguredMock(
                            Address::class,
                            [
                                'getCountry' => 'UA'
                            ]
                        )
                    ]
                ),
                [],
                'UA'
            ],
            [
                $this->createConfiguredMock(
                    Quote::class,
                    [
                        'getShippingAddress' => $this->createConfiguredMock(
                            Address::class,
                            [
                                'getCountry' => null
                            ]
                        ),
                        'getBillingAddress' => $this->createConfiguredMock(
                            Address::class,
                            [
                                'getCountry' => null
                            ]
                        )
                    ]
                ),
                $this->createConfiguredMock(
                    Customer::class,
                    [
                        'getPrimaryBillingAddress' => $this->createConfiguredMock(
                            Address::class,
                            [
                                'getCountry' => null
                            ]
                        )
                    ]
                ),
                ['country' => 'CA'],
                'CA'
            ]
        ];
    }
}
