<?php

namespace Ebizmarts\Notification\Test\Unit\Model\Module;

use Ebizmarts\Notification\Model\Module\Management;
use Magento\Framework\Module\ModuleList;
use PHPUnit\Framework\TestCase;

class ManagementTest extends TestCase
{
    /** @var ModuleList $moduleList|\PHPUnit_Framework_MockObject_MockObject */
    private $moduleList;

    /** @var string[] $availableModules|\PHPUnit_Framework_MockObject_MockObject */
    private $availableModules = [
        'mailchimp' => 'Ebizmarts_MailChimp',
        'opayo' => 'Ebizmarts_SagePaySuite',
        'brippo' => 'Ebizmarts_BrippoPaymentsFrontend',
        'pos' => 'Ebizmarts_Pos'
    ];

    /** @var Management $management */
    private $management;

    public function setUp(): void
    {
        $this->moduleList = $this->getMockBuilder(ModuleList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->management = new Management(
            $this->moduleList,
            $this->availableModules
        );
    }

    /**
     * @dataProvider dataProviderModules
     * @param $dataProvider
     */
    public function testGetEbizmartsModules($dataProvider)
    {
        $this->moduleList->expects($this->once())
            ->method('getAll')
            ->willReturn($dataProvider['moduleList']);

        $result = $this->management->getEbizmartsModules();
        $this->assertEquals($dataProvider['result'], $result);
    }

    /**
     * @dataProvider dataProviderFileModuleName
     * @param $dataProvider
     */
    public function testFileContainsModuleName($dataProvider)
    {
        $result = $this->management->fileContainsModuleName($dataProvider['file']);
        $this->assertEquals($dataProvider['result'], $result);
    }

    public function dataProviderFileModuleName()
    {
        return [
            'test1' => [
                [
                    'file' => 'opayo.xml',
                    'result' => true
                ]
            ],
            'test2' => [
                [
                    'file' => 'mailchimp.xml',
                    'result' => true
                ]
            ],
            'test3' => [
                [
                    'file' => 'pos.xml',
                    'result' => true
                ]
            ],
            'test4' => [
                [
                    'file' => 'brippo.xml',
                    'result' => true
                ]
            ],
            'test5' => [
                [
                    'file' => 'test.xml',
                    'result' => false
                ]
            ],
            'test6' => [
                [
                    'file' => '',
                    'result' => false
                ]
            ]
        ];
    }

    public function dataProviderModules()
    {
        return [
            'test1' => [
                [
                    'moduleList' => [
                        'Ebizmarts_MailChimp' => 'Ebizmarts_MailChimp',
                        'Ebizmarts_SagePaySuite' => 'Ebizmarts_SagePaySuite',
                        'Ebizmarts_BrippoPaymentsFrontend' => 'Ebizmarts_BrippoPaymentsFrontend',
                        'Ebizmarts_Pos' => 'Ebizmarts_Pos'
                    ],
                    'result' => [
                        'mailchimp.xml',
                        'opayo.xml',
                        'brippo.xml',
                        'pos.xml'
                    ]
                ]
            ],
            'test2' => [
                [
                    'moduleList' => [
                        'Ebizmarts_MailChimp' => 'Ebizmarts_MailChimp',
                        'Ebizmarts_SagePaySuite' => 'Ebizmarts_SagePaySuite',
                        'Ebizmarts_BrippoPaymentsFrontend' => 'Ebizmarts_BrippoPaymentsFrontend'
                    ],
                    'result' => [
                        'mailchimp.xml',
                        'opayo.xml',
                        'brippo.xml'
                    ]
                ]
            ],
            'test3' => [
                [
                    'moduleList' => [
                        'Ebizmarts_MailChimp' => 'Ebizmarts_MailChimp',
                        'Ebizmarts_SagePaySuite' => 'Ebizmarts_SagePaySuite'
                    ],
                    'result' => [
                        'mailchimp.xml',
                        'opayo.xml'
                    ]
                ]
            ],
            'test4' => [
                [
                    'moduleList' => [
                        'Ebizmarts_MailChimp' => 'Ebizmarts_MailChimp'
                    ],
                    'result' => [
                        'mailchimp.xml'
                    ]
                ]
            ],
            'test5' => [
                [
                    'moduleList' => [
                        'Test_Test' => 'Test_Test'
                    ],
                    'result' => [
                    ]
                ]
            ],
            'test6' => [
                [
                    'moduleList' => [
                    ],
                    'result' => [
                    ]
                ]
            ]
        ];
    }
}
