<?php

namespace Ebizmarts\SagePaySuiteFailedPaymentEmail\Test\Unit\Plugin;

use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuiteFailedPaymentEmail\Plugin\TrySendPiEmail;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\EcommerceManagement;
use Ebizmarts\SagePaySuiteFailedPaymentEmail\Api\Email\FailedPaymentInterface;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultInterface;
use Magento\Quote\Api\Data\CartInterface;
use PHPUnit\Framework\TestCase;

class TrySendPiEmailTest extends TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /** @var EcommerceManagement $ecommerceManagement|\PHPUnit_Framework_MockObject_MockObject */
    private $ecommerceManagement;

    /** @var FailedPaymentInterface $failedPaymentInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $failedPaymentInterface;

    /** @var PiTransactionResultInterface $piTransactionResultInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $piTransactionResultInterface;

    /** @var CartInterface $cartInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $cartInterface;

    /** @var TrySendPiEmail $trySendPiEmail */
    private $trySendPiEmail;

    public function setUp() : void
    {
        $this->configMock = $this
            ->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->ecommerceManagement = $this->getMockBuilder(EcommerceManagement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->failedPaymentInterface = $this->getMockBuilder(FailedPaymentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->piTransactionResultInterface = $this->getMockBuilder(PiTransactionResultInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cartInterface = $this->getMockBuilder(CartInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->trySendPiEmail = new TrySendPiEmail(
            $this->failedPaymentInterface,
            $this->configMock
        );
    }

    public function testAfterTryToVoidTransactionAndUpdateResult()
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

        $this->ecommerceManagement->expects($this->once())
            ->method('getPayResult')
            ->willReturn($this->piTransactionResultInterface);

        $this->ecommerceManagement->expects($this->once())
            ->method('isPaymentSuccessful')
            ->willReturn(false);

        $this->ecommerceManagement->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->cartInterface);

        $this->cartInterface->expects($this->once())
            ->method('getId')
            ->willReturn($quoteId);

        $this->ecommerceManagement->expects($this->once())
            ->method('getErrorMessage')
            ->willReturn($errorMessage);

        $this->failedPaymentInterface->expects($this->once())
            ->method('sendEmail')
            ->with($quoteId, $errorMessage)
            ->willReturnSelf();

        $this->trySendPiEmail->afterTryToVoidTransactionAndUpdateResult($this->ecommerceManagement);
    }

    public function testErrorMessageNotNull()
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

        $this->ecommerceManagement->expects($this->once())
            ->method('getPayResult')
            ->willReturn($this->piTransactionResultInterface);

        $this->ecommerceManagement->expects($this->once())
            ->method('isPaymentSuccessful')
            ->willReturn(false);

        $this->ecommerceManagement->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->cartInterface);

        $this->cartInterface->expects($this->once())
            ->method('getId')
            ->willReturn($quoteId);

        $this->ecommerceManagement->expects($this->never())
            ->method('getErrorMessage');

        $this->failedPaymentInterface->expects($this->once())
            ->method('sendEmail')
            ->with($quoteId, $errorMessage)
            ->willReturnSelf();

        $this->trySendPiEmail->afterTryToVoidTransactionAndUpdateResult($this->ecommerceManagement, $errorMessage);
    }

    public function testPaymentSuccessful()
    {
        $errorMessage = 'Payment not successful';
        $quoteId = 1899;

        $this->configMock->expects($this->once())
            ->method('setMethodCode')
            ->with(Config::METHOD_PI)
            ->willReturnSelf();

        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with('failed_payment_email')
            ->willReturn('1');

        $this->ecommerceManagement->expects($this->once())
            ->method('getPayResult')
            ->willReturn($this->piTransactionResultInterface);

        $this->ecommerceManagement->expects($this->once())
            ->method('isPaymentSuccessful')
            ->willReturn(Config::SUCCESS_STATUS);

        $this->ecommerceManagement->expects($this->never())
            ->method('getQuote');

        $this->cartInterface->expects($this->never())
            ->method('getId');

        $this->ecommerceManagement->expects($this->never())
            ->method('getErrorMessage');

        $this->failedPaymentInterface->expects($this->never())
            ->method('sendEmail');

        $this->trySendPiEmail->afterTryToVoidTransactionAndUpdateResult($this->ecommerceManagement, $errorMessage);
    }

    public function testPayResultIsNull()
    {
        $errorMessage = 'Payment not successful';

        $this->configMock->expects($this->once())
            ->method('setMethodCode')
            ->with(Config::METHOD_PI)
            ->willReturnSelf();

        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with("failed_payment_email")
            ->willReturn("1");

        $this->ecommerceManagement->expects($this->once())
            ->method('getPayResult')
            ->willReturn(null);

        $this->ecommerceManagement->expects($this->never())
            ->method('isPaymentSuccessful');

        $this->ecommerceManagement->expects($this->never())
            ->method('getQuote');

        $this->cartInterface->expects($this->never())
            ->method('getId');

        $this->ecommerceManagement->expects($this->never())
            ->method('getErrorMessage');

        $this->failedPaymentInterface->expects($this->never())
            ->method('sendEmail');

        $this->trySendPiEmail->afterTryToVoidTransactionAndUpdateResult($this->ecommerceManagement, $errorMessage);
    }

    public function testSettingNotActive()
    {
        $errorMessage = 'Payment not successful';

        $this->configMock->expects($this->once())
            ->method('setMethodCode')
            ->with(Config::METHOD_PI)
            ->willReturnSelf();

        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with("failed_payment_email")
            ->willReturn("0");

        $this->ecommerceManagement->expects($this->never())
            ->method('getPayResult');

        $this->ecommerceManagement->expects($this->never())
            ->method('isPaymentSuccessful');

        $this->ecommerceManagement->expects($this->never())
            ->method('getQuote');

        $this->cartInterface->expects($this->never())
            ->method('getId');

        $this->ecommerceManagement->expects($this->never())
            ->method('getErrorMessage');

        $this->failedPaymentInterface->expects($this->never())
            ->method('sendEmail');

        $this->trySendPiEmail->afterTryToVoidTransactionAndUpdateResult($this->ecommerceManagement, $errorMessage);
    }
}
