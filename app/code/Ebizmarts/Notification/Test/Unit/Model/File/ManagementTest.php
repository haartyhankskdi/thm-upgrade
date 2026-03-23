<?php

namespace Ebizmarts\Notification\Test\Unit\Model\File;

use Ebizmarts\Notification\Model\File\Management;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Framework\Xml\Parser;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\TestCase;

class ManagementTest extends TestCase
{
    /** @var File $file|\PHPUnit_Framework_MockObject_MockObject */
    private $file;

    /** @var Filesystem  $filesystem|\PHPUnit_Framework_MockObject_MockObject */
    private $filesystem;

    /** @var Parser $xmlParser|\PHPUnit_Framework_MockObject_MockObject */
    private $xmlParser;

    /** @var DirectoryList $directoryList|\PHPUnit_Framework_MockObject_MockObject */
    private $directoryList;

    /** @var IoFile $ioFile|\PHPUnit_Framework_MockObject_MockObject */
    private $ioFile;

    /** @var WriteInterface $writeInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $writeInterface;

    /** @var Management $management */
    private $management;

    public function setUp(): void
    {
        $this->file = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->xmlParser = $this->getMockBuilder(Parser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->directoryList = $this->getMockBuilder(DirectoryList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->ioFile = $this->getMockBuilder(IoFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->writeInterface = $this->getMockBuilder(WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->management = new Management(
            $this->file,
            $this->filesystem,
            $this->xmlParser,
            $this->directoryList,
            $this->ioFile
        );
    }

    /**
     * @dataProvider dataProviderFileNotification
     * @param $dataProvider
     */
    public function testSaveXmlFile($dataProvider)
    {
        $this->filesystem->expects($this->exactly($dataProvider['writeInterfaceExecutionTimes']))
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($this->writeInterface);

        $this->writeInterface->expects($this->once())
            ->method('isExist')
            ->with(Management::NOTIFICATIONS_PATH)
            ->willReturn($dataProvider['pathExist']);

        $this->writeInterface->expects($this->exactly($dataProvider['createPathExecutionTimes']))
            ->method('create')
            ->with(Management::NOTIFICATIONS_PATH)
            ->willReturnSelf();

        $this->writeInterface->expects($this->exactly($dataProvider['createPathExecutionTimes']))
            ->method('changePermissions')
            ->with(Management::NOTIFICATIONS_PATH, Management::STORAGE_PATH_PERMISSIONS)
            ->willReturnSelf();

        $this->directoryList->expects($this->once())
            ->method('getPath')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($dataProvider['magentoPath']);

        $this->file->expects($this->once())
            ->method('isExists')
            ->willReturn($dataProvider['fileExist']);

        $this->file->expects($this->exactly($dataProvider['deleteFileExecutionTimes']))
            ->method('deleteFile')
            ->with($dataProvider['fileName'])
            ->willReturnSelf();

        $this->ioFile->expects($this->once())
            ->method('write')
            ->with(
                $dataProvider['fileName'],
                $dataProvider['contentAfterRemoveUnnesessaryContent']
            )->willReturnSelf();

        $this->management->saveXmlFile(
            $dataProvider['module'],
            $dataProvider['contentBeforeRemoveUnnesessaryContent']
        );
    }

    /**
     * @dataProvider dataProviderFile
     * @param $dataProvider
     */
    public function testGetXmlArray($dataProvider)
    {
        $this->xmlParser->expects($this->once())
            ->method('load')
            ->with($dataProvider['file'])
            ->willReturnSelf();

        $this->xmlParser->expects($this->once())
            ->method('xmlToArray')
            ->willReturn($dataProvider['arrayContent']);

        $this->management->getXmlArray($dataProvider['file']);
    }

    /**
     * @dataProvider dataProviderAllFiles
     * @param $dataProvider
     */
    public function testGetAllFiles($dataProvider)
    {
        $this->file->expects($this->exactly($dataProvider['readDirectoryExecutionTimes']))
            ->method('readDirectory')
            ->with($dataProvider['directoryPath'])
            ->willReturn($dataProvider['result']);

        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($this->writeInterface);

        $this->writeInterface->expects($this->once())
            ->method('isExist')
            ->with($dataProvider['directoryPathIsExist'])
            ->willReturn($dataProvider['directoryExists']);

        $result = $this->management->getAllFiles($dataProvider['directoryPath']);
        $this->assertEquals($dataProvider['result'], $result);
    }

    /**
     * @dataProvider dataProviderMagentoPath
     * @param $dataProvider
     */

    public function testGetMagentoPath($dataProvider)
    {
        $this->directoryList->expects($this->once())
            ->method('getPath')
            ->with($dataProvider['directoryCode'])
            ->willReturn($dataProvider['result']);

        $result = $this->management->getMagentoPath($dataProvider['directoryCode']);
        $this->assertEquals($dataProvider['result'], $result);
    }

    public function dataProviderMagentoPath()
    {
        return [
            'test1' => [
                [
                    'directoryCode' => DirectoryList::VAR_DIR,
                    'result' => '/var/www/html/opayo245/var/notifications/mailchimp.xml'
                ]
            ],
            'test2' => [
                [
                    'directoryCode' => DirectoryList::VAR_DIR,
                    'result' => ''
                ]
            ],
            'test3' => [
                [
                    'directoryCode' => DirectoryList::VAR_DIR,
                    'result' => null
                ]
            ],
            'test4' => [
                [
                    'directoryCode' => '',
                    'result' => null
                ]
            ],
            'test5' => [
                [
                    'directoryCode' => null,
                    'result' => null
                ]
            ]
        ];
    }

    public function dataProviderAllFiles()
    {
        return [
            'test1' => [
                [
                    'readDirectoryExecutionTimes' => 1,
                    'directoryPath' => '/var/www/html/opayo245/var/notifications',
                    'result' => [
                        '/var/www/html/opayo245/var/notifications/mailchimp.xml',
                        '/var/www/html/opayo245/var/notifications/opdayo.xml',
                        '/var/www/html/opayo245/var/notifications/test.xml',
                        '/var/www/html/opayo245/var/notifications/pos.xml',
                        '/var/www/html/opayo245/var/notifications/brippo.xml'
                    ],
                    'directoryExists' => true,
                    'directoryPathIsExist' => '/notifications/'
                ]
            ],
            'test2' => [
                [
                    'readDirectoryExecutionTimes' => 1,
                    'directoryPath' => '/var/www/html/opayo245/var/notifications',
                    'result' => [],
                    'directoryExists' => true,
                    'directoryPathIsExist' => '/notifications/'
                ]
            ],
            'test3' => [
                [
                    'readDirectoryExecutionTimes' => 1,
                    'directoryPath' => '',
                    'result' => [],
                    'directoryExists' => true,
                    'directoryPathIsExist' => '/notifications/'
                ]
            ],
            'test4' => [
                [
                    'readDirectoryExecutionTimes' => 0,
                    'directoryPath' => '',
                    'result' => [],
                    'directoryExists' => false,
                    'directoryPathIsExist' => '/notifications/'
                ]
            ],
            'test5' => [
                [
                    'readDirectoryExecutionTimes' => 0,
                    'directoryPath' => null,
                    'result' => [],
                    'directoryExists' => false,
                    'directoryPathIsExist' => '/notifications/'
                ]
            ]
        ];
    }

    public function dataProviderFile()
    {
        return [
            'test1' => [
                [
                    'file' => '/var/www/html/opayo245/var/notifications/mailchimp.xml',
                    'arrayContent' => [
                        'notifications' => []
                    ]
                ]
            ],
            'test2' => [
                [
                    'file' => '/var/www/html/opayo245/var/notifications/opayo.xml',
                    'arrayContent' => [
                        'notifications' => [
                            'opayo1' => [
                                'id' => 1,
                                'title' => 'testeando updated at',
                                'description' => 'testeando updated at testeando updated at testeando updated',
                                'created_at' => '2023-03-30 13:59:58',
                                'updated_at' => '2023-03-30 14:09:26',
                                'severity' => 'Critical',
                                'url' => 'https://github.com/ebizmarts/magento2-sage-pay-suite',
                                'module' => 'opayo'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function dataProviderFileNotification()
    {
        return [
            'test1' => [
                [
                    'module' => 'mailchimp.xml',
                    'magentoPath' => '/var/www/html/opayo245/var',
                    'fileName' => '/var/www/html/opayo245/var/notifications/mailchimp.xml',
                    'contentBeforeRemoveUnnesessaryContent' => 'HTTP/1.1 200 OK'
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
                    'contentAfterRemoveUnnesessaryContent' => '<notifications/>',
                    'pathExist' => false,
                    'createPathExecutionTimes' => 1,
                    'writeInterfaceExecutionTimes' => 2,
                    'fileExist' => true,
                    'deleteFileExecutionTimes' => 1
                ]
            ],
            'test2' => [
                [
                    'module' => 'opayo.xml',
                    'magentoPath' => '/var/www/html/opayo245/var',
                    'fileName' => '/var/www/html/opayo245/var/notifications/opayo.xml',
                    'contentBeforeRemoveUnnesessaryContent' => 'HTTP/1.1 200 OK'
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
                        . '<notifications><opayo1><id>1</id><title>testeando updated at</title>'
                        . '<description>testeando updated at testeando updated at testeando updated attesteando updated'
                        . '</description><created_at>2023-03-30 13:59:58</created_at>'
                        . '<updated_at>2023-03-30 14:09:26</updated_at><severity>Critical</severity>'
                        . '<url>https://github.com/ebizmarts/magento2-sage-pay-suite</url><module>opayo</module>'
                        . '</opayo1><opayo3><id>3</id><title>testeando notificacion modificada</title>'
                        . '<description>para el mismo modulo con otra severity</description><created_at>'
                        . '2023-03-30 14:01:56</created_at><updated_at>2023-03-30 14:23:08</updated_at>'
                        . '<severity>Major</severity><url>https://github.com/ebizmarts/magento2-sage-pay-suite</url>'
                        . '<module>opayo</module></opayo3><opayo4><id>4</id><title>testenado new notification</title>'
                        . '<description>test new notification and modification</description>'
                        . '<created_at>2023-03-30 14:21:41</created_at><updated_at>2023-03-30 14:21:41</updated_at>'
                        . '<severity>Notice</severity><url>https://github.com/ebizmarts/magento2-sage-pay-suite</url>'
                        . '<module>opayo</module></opayo4></notifications>',
                    'contentAfterRemoveUnnesessaryContent' => '<notifications><opayo1><id>1</id>'
                        . '<title>testeando updated at</title>'
                        . '<description>testeando updated at testeando updated at testeando updated attesteando updated'
                        . '</description><created_at>2023-03-30 13:59:58</created_at>'
                        . '<updated_at>2023-03-30 14:09:26</updated_at><severity>Critical</severity>'
                        . '<url>https://github.com/ebizmarts/magento2-sage-pay-suite</url><module>opayo</module>'
                        . '</opayo1><opayo3><id>3</id><title>testeando notificacion modificada</title>'
                        . '<description>para el mismo modulo con otra severity</description><created_at>'
                        . '2023-03-30 14:01:56</created_at><updated_at>2023-03-30 14:23:08</updated_at>'
                        . '<severity>Major</severity><url>https://github.com/ebizmarts/magento2-sage-pay-suite</url>'
                        . '<module>opayo</module></opayo3><opayo4><id>4</id><title>testenado new notification</title>'
                        . '<description>test new notification and modification</description>'
                        . '<created_at>2023-03-30 14:21:41</created_at><updated_at>2023-03-30 14:21:41</updated_at>'
                        . '<severity>Notice</severity><url>https://github.com/ebizmarts/magento2-sage-pay-suite</url>'
                        . '<module>opayo</module></opayo4></notifications>',
                    'pathExist' => false,
                    'createPathExecutionTimes' => 1,
                    'writeInterfaceExecutionTimes' => 2,
                    'fileExist' => true,
                    'deleteFileExecutionTimes' => 1
                ]
            ],
            'test3' => [
                [
                    'module' => 'mailchimp.xml',
                    'magentoPath' => '/var/www/html/opayo245/var',
                    'fileName' => '/var/www/html/opayo245/var/notifications/mailchimp.xml',
                    'contentBeforeRemoveUnnesessaryContent' => 'HTTP/1.1 200 OK'
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
                    'contentAfterRemoveUnnesessaryContent' => '<notifications/>',
                    'pathExist' => true,
                    'createPathExecutionTimes' => 0,
                    'writeInterfaceExecutionTimes' => 1,
                    'fileExist' => true,
                    'deleteFileExecutionTimes' => 1
                ]
            ],
            'test4' => [
                [
                    'module' => 'opayo.xml',
                    'magentoPath' => '/var/www/html/opayo245/var',
                    'fileName' => '/var/www/html/opayo245/var/notifications/opayo.xml',
                    'contentBeforeRemoveUnnesessaryContent' => 'HTTP/1.1 200 OK'
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
                        . '<notifications><opayo1><id>1</id><title>testeando updated at</title>'
                        . '<description>testeando updated at testeando updated at testeando updated attesteando updated'
                        . '</description><created_at>2023-03-30 13:59:58</created_at>'
                        . '<updated_at>2023-03-30 14:09:26</updated_at><severity>Critical</severity>'
                        . '<url>https://github.com/ebizmarts/magento2-sage-pay-suite</url><module>opayo</module>'
                        . '</opayo1><opayo3><id>3</id><title>testeando notificacion modificada</title>'
                        . '<description>para el mismo modulo con otra severity</description><created_at>'
                        . '2023-03-30 14:01:56</created_at><updated_at>2023-03-30 14:23:08</updated_at>'
                        . '<severity>Major</severity><url>https://github.com/ebizmarts/magento2-sage-pay-suite</url>'
                        . '<module>opayo</module></opayo3><opayo4><id>4</id><title>testenado new notification</title>'
                        . '<description>test new notification and modification</description>'
                        . '<created_at>2023-03-30 14:21:41</created_at><updated_at>2023-03-30 14:21:41</updated_at>'
                        . '<severity>Notice</severity><url>https://github.com/ebizmarts/magento2-sage-pay-suite</url>'
                        . '<module>opayo</module></opayo4></notifications>',
                    'contentAfterRemoveUnnesessaryContent' => '<notifications><opayo1><id>1</id>'
                        . '<title>testeando updated at</title>'
                        . '<description>testeando updated at testeando updated at testeando updated attesteando updated'
                        . '</description><created_at>2023-03-30 13:59:58</created_at>'
                        . '<updated_at>2023-03-30 14:09:26</updated_at><severity>Critical</severity>'
                        . '<url>https://github.com/ebizmarts/magento2-sage-pay-suite</url><module>opayo</module>'
                        . '</opayo1><opayo3><id>3</id><title>testeando notificacion modificada</title>'
                        . '<description>para el mismo modulo con otra severity</description><created_at>'
                        . '2023-03-30 14:01:56</created_at><updated_at>2023-03-30 14:23:08</updated_at>'
                        . '<severity>Major</severity><url>https://github.com/ebizmarts/magento2-sage-pay-suite</url>'
                        . '<module>opayo</module></opayo3><opayo4><id>4</id><title>testenado new notification</title>'
                        . '<description>test new notification and modification</description>'
                        . '<created_at>2023-03-30 14:21:41</created_at><updated_at>2023-03-30 14:21:41</updated_at>'
                        . '<severity>Notice</severity><url>https://github.com/ebizmarts/magento2-sage-pay-suite</url>'
                        . '<module>opayo</module></opayo4></notifications>',
                    'pathExist' => true,
                    'createPathExecutionTimes' => 0,
                    'writeInterfaceExecutionTimes' => 1,
                    'fileExist' => true,
                    'deleteFileExecutionTimes' => 1
                ]
            ],
            'test5' => [
                [
                    'module' => 'mailchimp.xml',
                    'magentoPath' => '/var/www/html/opayo245/var',
                    'fileName' => '/var/www/html/opayo245/var/notifications/mailchimp.xml',
                    'contentBeforeRemoveUnnesessaryContent' => 'HTTP/1.1 200 OK'
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
                    'contentAfterRemoveUnnesessaryContent' => '<notifications/>',
                    'pathExist' => true,
                    'createPathExecutionTimes' => 0,
                    'writeInterfaceExecutionTimes' => 1,
                    'fileExist' => false,
                    'deleteFileExecutionTimes' => 0
                ]
            ],
            'test6' => [
                [
                    'module' => 'opayo.xml',
                    'magentoPath' => '/var/www/html/opayo245/var',
                    'fileName' => '/var/www/html/opayo245/var/notifications/opayo.xml',
                    'contentBeforeRemoveUnnesessaryContent' => 'HTTP/1.1 200 OK'
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
                        . '<notifications><opayo1><id>1</id><title>testeando updated at</title>'
                        . '<description>testeando updated at testeando updated at testeando updated attesteando updated'
                        . '</description><created_at>2023-03-30 13:59:58</created_at>'
                        . '<updated_at>2023-03-30 14:09:26</updated_at><severity>Critical</severity>'
                        . '<url>https://github.com/ebizmarts/magento2-sage-pay-suite</url><module>opayo</module>'
                        . '</opayo1><opayo3><id>3</id><title>testeando notificacion modificada</title>'
                        . '<description>para el mismo modulo con otra severity</description><created_at>'
                        . '2023-03-30 14:01:56</created_at><updated_at>2023-03-30 14:23:08</updated_at>'
                        . '<severity>Major</severity><url>https://github.com/ebizmarts/magento2-sage-pay-suite</url>'
                        . '<module>opayo</module></opayo3><opayo4><id>4</id><title>testenado new notification</title>'
                        . '<description>test new notification and modification</description>'
                        . '<created_at>2023-03-30 14:21:41</created_at><updated_at>2023-03-30 14:21:41</updated_at>'
                        . '<severity>Notice</severity><url>https://github.com/ebizmarts/magento2-sage-pay-suite</url>'
                        . '<module>opayo</module></opayo4></notifications>',
                    'contentAfterRemoveUnnesessaryContent' => '<notifications><opayo1><id>1</id>'
                        . '<title>testeando updated at</title>'
                        . '<description>testeando updated at testeando updated at testeando updated attesteando updated'
                        . '</description><created_at>2023-03-30 13:59:58</created_at>'
                        . '<updated_at>2023-03-30 14:09:26</updated_at><severity>Critical</severity>'
                        . '<url>https://github.com/ebizmarts/magento2-sage-pay-suite</url><module>opayo</module>'
                        . '</opayo1><opayo3><id>3</id><title>testeando notificacion modificada</title>'
                        . '<description>para el mismo modulo con otra severity</description><created_at>'
                        . '2023-03-30 14:01:56</created_at><updated_at>2023-03-30 14:23:08</updated_at>'
                        . '<severity>Major</severity><url>https://github.com/ebizmarts/magento2-sage-pay-suite</url>'
                        . '<module>opayo</module></opayo3><opayo4><id>4</id><title>testenado new notification</title>'
                        . '<description>test new notification and modification</description>'
                        . '<created_at>2023-03-30 14:21:41</created_at><updated_at>2023-03-30 14:21:41</updated_at>'
                        . '<severity>Notice</severity><url>https://github.com/ebizmarts/magento2-sage-pay-suite</url>'
                        . '<module>opayo</module></opayo4></notifications>',
                    'pathExist' => true,
                    'createPathExecutionTimes' => 0,
                    'writeInterfaceExecutionTimes' => 1,
                    'fileExist' => false,
                    'deleteFileExecutionTimes' => 0
                ]
            ],
            'test7' => [
                [
                    'module' => '',
                    'magentoPath' => '/var/www/html/opayo245/var',
                    'fileName' => '/var/www/html/opayo245/var/notifications/',
                    'contentBeforeRemoveUnnesessaryContent' => '',
                    'contentAfterRemoveUnnesessaryContent' => '',
                    'pathExist' => true,
                    'createPathExecutionTimes' => 0,
                    'writeInterfaceExecutionTimes' => 1,
                    'fileExist' => false,
                    'deleteFileExecutionTimes' => 0
                ]
            ]
        ];
    }
}
