<?php

namespace Ebizmarts\SagePaySuite\Cron;

use Ebizmarts\SagePaySuite\Api\TokenModelInterface;
use Ebizmarts\SagePaySuite\Api\TokenModelInterfaceFactory;
use Ebizmarts\SagePaySuite\Api\Data\TokenInterface;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;

class RemoveExpiredTokens
{
    /** @var TokenModelInterfaceFactory $tokenModelInterfaceFactory */
    private $tokenModelInterfaceFactory;

    /** @var Logger $suiteLogger */
    private $suiteLogger;

    /**
     * RemoveExpiredTokens constructor.
     *
     * @param TokenModelInterfaceFactory $tokenModelInterfaceFactory
     * @param Logger $suiteLogger
     */
    public function __construct(
        TokenModelInterfaceFactory $tokenModelInterfaceFactory,
        Logger $suiteLogger
    ) {
        $this->tokenModelInterfaceFactory = $tokenModelInterfaceFactory;
        $this->suiteLogger = $suiteLogger;
    }

    public function process()
    {
        try {
            /** @var TokenModelInterface $tokenModelInterface */
            $tokenModelInterface = $this->tokenModelInterfaceFactory->create();
            $expiredTokens = $tokenModelInterface->getExpiredTokens(Date('Y-m-01'));
            /** @var TokenInterface $expiredToken */
            foreach ($expiredTokens as $expiredToken) {
                $this->suiteLogger->sageLog(Logger::LOG_CRON, $expiredToken, [__METHOD__, __LINE__]);
                try {
                    /** @var TokenModelInterface $tokenModelInterface */
                    $tokenModel = $this->tokenModelInterfaceFactory->create();
                    $tokenModel = $tokenModel->loadToken($expiredToken->getId());
                    $tokenModel->deleteToken();
                } catch (\Exception $exception) {
                    $this->suiteLogger->sageLog(Logger::LOG_CRON, [
                        "TokenId" => $expiredToken->getEntityId(),
                        "Result"  => $exception->getMessage(),
                        "Trace"   => $exception->getTraceAsString()
                    ], [__METHOD__, __LINE__]);
                }
            }
        } catch (\Exception $exception) {
            $this->suiteLogger->sageLog(Logger::LOG_CRON, [
                "Result"  => $exception->getMessage(),
                "Trace"   => $exception->getTraceAsString()
            ], [__METHOD__, __LINE__]);
        }
    }
}
