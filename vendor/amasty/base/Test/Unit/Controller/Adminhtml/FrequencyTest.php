<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Test\Unit\Controller\Adminhtml;

use Amasty\Base\Controller\Adminhtml\Notification\Frequency;
use Amasty\Base\Model\Config;
use Amasty\Base\Model\Source\Frequency as FrequencySource;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FrequencyTest extends TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var RedirectFactory|MockObject
     */
    private $redirectFactoryMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var FrequencySource|MockObject
     */
    private $frequencySourceMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $redirectMock = $this->createMock(Redirect::class);
        $this->redirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->redirectFactoryMock->method('create')->willReturn($redirectMock);
        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->method('getResultRedirectFactory')->willReturn($this->redirectFactoryMock);

        $this->configMock = $this->createMock(Config::class);
        $this->frequencySourceMock = $this->createMock(FrequencySource::class);
        $this->frequencySourceMock->method('toOptionArray')->willReturn($this->getFrequencyOptions());
    }

    public function testIncreaseFrequency(): void
    {
        $model = new Frequency($this->contextMock, $this->configMock, $this->frequencySourceMock);

        $this->requestMock->method('getParam')->with('action')->willReturn('less');
        $this->configMock->method('getCurrentFrequencyValue')->willReturn(5);
        $this->configMock->expects($this->once())->method('changeFrequency')->with(10);
        $this->messageManagerMock->expects($this->once())->method('addSuccessMessage')->with(
            __('You will get less messages of this type. Notification frequency has been updated.')
        );

        $model->execute();
    }

    public function testDecreaseFrequency(): void
    {
        $model = new Frequency($this->contextMock, $this->configMock, $this->frequencySourceMock);

        $this->requestMock->method('getParam')->with('action')->willReturn('more');
        $this->configMock->method('getCurrentFrequencyValue')->willReturn(5);
        $this->configMock->expects($this->once())->method('changeFrequency')->with(2);
        $this->messageManagerMock->expects($this->once())->method('addSuccessMessage')->with(
            __('You will get more messages of this type. Notification frequency has been updated.')
        );

        $model->execute();
    }

    public function testError(): void
    {
        $model = new Frequency($this->contextMock, $this->configMock, $this->frequencySourceMock);

        $this->configMock->expects($this->never())->method('changeFrequency');
        $this->messageManagerMock->expects($this->once())->method('addErrorMessage')->with(
            __('An error occurred while changing the frequency.')
        );

        $model->execute();
    }

    private function getFrequencyOptions(): array
    {
        return [
            [
                'value' => 2,
                'label' => __('2 days')
            ],
            [
                'value' => 5,
                'label' => __('5 days')
            ],
            [
                'value' => 10,
                'label' => __('10 days')
            ],
            [
                'value' => 15,
                'label' => __('15 days')
            ],
            [
                'value' => 30,
                'label' => __('30 days')
            ]
        ];
    }
}
