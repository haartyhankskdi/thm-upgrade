<?php

namespace Ebizmarts\Notification\Test\Unit\Cron;

use Ebizmarts\Notification\Cron\Notification;
use Ebizmarts\Notification\Model\File\Management as FileManager;
use Ebizmarts\Notification\Model\Module\Management as ModulesManagement;
use Magento\Framework\HTTP\Adapter\Curl as CurlAdapter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class NotificationTest extends TestCase
{
    /** @var FileManager $fileManager|\PHPUnit_Framework_MockObject_MockObject */
    private $fileManager;

    /** @var ModulesManagement $modulesManagement|\PHPUnit_Framework_MockObject_MockObject */
    private $modulesManagement;

    /** @var LoggerInterface $loggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $loggerInterface;

    /** @var CurlAdapter $curl|\PHPUnit_Framework_MockObject_MockObject */
    private $curl;

    /** @var Notification $cron */
    private $cron;

    public function setUp(): void
    {
        $this->fileManager = $this->getMockBuilder(FileManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->modulesManagement = $this->getMockBuilder(ModulesManagement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerInterface = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->curl = $this->getMockBuilder(CurlAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cron = new Notification(
            $this->fileManager,
            $this->modulesManagement,
            $this->loggerInterface,
            $this->curl
        );
    }

    /**
     * @dataProvider dataProviderProcessCron
     * @param $dataProvider
     */
    public function testProcess($dataProvider)
    {
        $config = [
            'timeout'    => 120,
            'verifyhost' => 2,
        ];

        $this->modulesManagement->expects($this->once())
            ->method('getEbizmartsModules')
            ->willReturn($dataProvider['resultModuleList']);

        $this->curl->expects($this->exactly($dataProvider['executionTimes']))
            ->method('setConfig')
            ->with($config)
            ->willReturnSelf();

        $this->curl->expects($this->exactly($dataProvider['executionTimes']))
            ->method('write')
            ->willReturnSelf();

        $this->curl->expects($this->exactly($dataProvider['executionTimes']))
            ->method('read')
            ->willReturn($dataProvider['curlResult']);

        $this->fileManager->expects($this->exactly($dataProvider['executionTimes']))
            ->method('saveXmlFile')
            ->willReturnSelf();

        $this->cron->process();
    }

    public function dataProviderProcessCron()
    {
        return [
            'test1' => [
                [
                    'resultModuleList' => [
                        'mailchimp.xml',
                        'opayo.xml',
                        'brippo.xml',
                        'pos.xml'
                    ],
                    'curlResult' => 'HTTP/1.1 200 OK'
                        . 'Accept-Ranges: bytes'
                        . 'Content-Length: 73'
                        . 'Content-Type: application/xml'
                        . 'Date: Mon, 03 Apr 2023 12:36:33 GMT'
                        . 'Etag: "49-5f83756c20980"'
                        . 'Last-Modified: Fri, 31 Mar 2023 19:37:26 GMT'
                        . 'Ngrok-Trace-Id: b1808c59928ba1426ddf275a6556ccda'
                        . 'Server: Apache/2.4.54 (Unix) PHP/7.4.33'
                        . 'X-Frame-Options: SAMEORIGIN'
                        . '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                        . '<notifications/>',
                    'executionTimes' => 4
                ]
            ],
            'test2' => [
                [
                    'resultModuleList' => [
                        'mailchimp.xml',
                        'opayo.xml',
                        'brippo.xml'
                    ],
                    'curlResult' => 'HTTP/1.1 200 OK'
                        . 'Accept-Ranges: bytes'
                        . 'Content-Length: 73'
                        . 'Content-Type: application/xml'
                        . 'Date: Mon, 03 Apr 2023 12:36:33 GMT'
                        . 'Etag: "49-5f83756c20980"'
                        . 'Last-Modified: Fri, 31 Mar 2023 19:37:26 GMT'
                        . 'Ngrok-Trace-Id: b1808c59928ba1426ddf275a6556ccda'
                        . 'Server: Apache/2.4.54 (Unix) PHP/7.4.33'
                        . 'X-Frame-Options: SAMEORIGIN'
                        . '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                        . '<notifications/>',
                    'executionTimes' => 3
                ]
            ],
            'test3' => [
                [
                    'resultModuleList' => [
                        'mailchimp.xml',
                        'opayo.xml'
                    ],
                    'curlResult' => 'HTTP/1.1 200 OK'
                        . 'Accept-Ranges: bytes'
                        . 'Content-Length: 73'
                        . 'Content-Type: application/xml'
                        . 'Date: Mon, 03 Apr 2023 12:36:33 GMT'
                        . 'Etag: "49-5f83756c20980"'
                        . 'Last-Modified: Fri, 31 Mar 2023 19:37:26 GMT'
                        . 'Ngrok-Trace-Id: b1808c59928ba1426ddf275a6556ccda'
                        . 'Server: Apache/2.4.54 (Unix) PHP/7.4.33'
                        . 'X-Frame-Options: SAMEORIGIN'
                        . '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                        . '<notifications/>',
                    'executionTimes' => 2
                ]
            ],
            'test4' => [
                [
                    'resultModuleList' => [
                        'mailchimp.xml'
                    ],
                    'curlResult' => 'HTTP/1.1 200 OK'
                        . 'Accept-Ranges: bytes'
                        . 'Content-Length: 73'
                        . 'Content-Type: application/xml'
                        . 'Date: Mon, 03 Apr 2023 12:36:33 GMT'
                        . 'Etag: "49-5f83756c20980"'
                        . 'Last-Modified: Fri, 31 Mar 2023 19:37:26 GMT'
                        . 'Ngrok-Trace-Id: b1808c59928ba1426ddf275a6556ccda'
                        . 'Server: Apache/2.4.54 (Unix) PHP/7.4.33'
                        . 'X-Frame-Options: SAMEORIGIN'
                        . '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                        . '<notifications/>',
                    'executionTimes' => 1
                ]
            ],
            'test5' => [
                [
                    'resultModuleList' => [
                    ],
                    'curlResult' => '',
                    'executionTimes' => 0
                ]
            ]
        ];
    }
}
