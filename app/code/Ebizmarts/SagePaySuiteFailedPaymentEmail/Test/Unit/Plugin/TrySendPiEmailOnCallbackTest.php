<?php

namespace Ebizmarts\SagePaySuiteFailedPaymentEmail\Test\Unit\Plugin;

use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuiteFailedPaymentEmail\Plugin\TrySendPiEmailOnCallback;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\ThreeDSecureCallbackManagement;
use Ebizmarts\SagePaySuiteFailedPaymentEmail\Api\Email\FailedPaymentInterface;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultInterface;
use Ebizmarts\SagePaySuite\Api\Data\PiResultInterface;
use PHPUnit\Framework\TestCase;

class TrySendPiEmailOnCallbackTest extends TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /** @var ThreeDSecureCallbackManagement $threeDSecureCallbackManagement |\PHPUnit_Framework_MockObject_MockObject */
    private $threeDSecureCallbackManagement;

    /** @var FailedPaymentInterface $failedPaymentInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $failedPaymentInterface;

    /** @var PiTransactionResultInterface $piTransactionResultInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $piTransactionResultInterface;

    /** @var PiResultInterface $piResultInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $piResultInterface;

    /** @var TrySendPiEmailOnCallback $trySendPiEmailOnCallback */
    private $trySendPiEmailOnCallback;

    public function setUp() : void
    {
        $this->configMock = $this
            ->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->threeDSecureCallbackManagement = $this->getMockBuilder(ThreeDSecureCallbackManagement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->failedPaymentInterface = $this->getMockBuilder(FailedPaymentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->piTransactionResultInterface = $this->getMockBuilder(PiTransactionResultInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->piResultInterface = $this->getMockBuilder(PiResultInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->trySendPiEmailOnCallback = new TrySendPiEmailOnCallback(
            $this->failedPaymentInterface,
            $this->configMock
        );
    }

    public function testAfterPlaceOrder()
    {
        $errorMessage = 'Payment not successful';
        $quoteId = 1899;


        $this->configMock->expects($this->once())
            ->method('setMethodCode')
            ->with(Config::METHOD_PI)
            ->willReturnSelf();

        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with("failed_payment_email")
            ->willReturn("1");

        $this->piResultInterface->expects($this->once())
            ->method('getErrorMessage')
            ->willReturn($errorMessage);

        $this->threeDSecureCallbackManagement->expects($this->once())
            ->method('getQuoteIdFromParams')
            ->willReturn($quoteId);

        $this->failedPaymentInterface->expects($this->once())
            ->method('sendEmail')
            ->with($quoteId, $errorMessage)
            ->willReturnSelf();

        $this->trySendPiEmailOnCallback->afterPlaceOrder($this->threeDSecureCallbackManagement, $this->piResultInterface);
    }

    public function testSettingNotActive()
    {
        $quoteId = 1899;

        $this->configMock->expects($this->once())
            ->method('setMethodCode')
            ->with(Config::METHOD_PI)
            ->willReturnSelf();

        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with("failed_payment_email")
            ->willReturn("0");

        $this->piResultInterface->expects($this->never())
            ->method('getErrorMessage');

        $this->threeDSecureCallbackManagement->expects($this->never())
            ->method('getQuoteIdFromParams');

        $this->failedPaymentInterface->expects($this->never())
            ->method('sendEmail');

        $this->trySendPiEmailOnCallback->afterPlaceOrder($this->threeDSecureCallbackManagement, $this->piResultInterface);
    }

    public function testErrorMessageNotNull()
    {
        $quoteId = 1899;

        $this->configMock->expects($this->once())
            ->method('setMethodCode')
            ->with(Config::METHOD_PI)
            ->willReturnSelf();

        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with("failed_payment_email")
            ->willReturn("1");

        $this->piResultInterface->expects($this->once())
            ->method('getErrorMessage')
            ->willReturn(null);

        $this->threeDSecureCallbackManagement->expects($this->never())
            ->method('getQuoteIdFromParams');

        $this->failedPaymentInterface->expects($this->never())
            ->method('sendEmail');

        $this->trySendPiEmailOnCallback->afterPlaceOrder($this->threeDSecureCallbackManagement, $this->piResultInterface);
    }

}
