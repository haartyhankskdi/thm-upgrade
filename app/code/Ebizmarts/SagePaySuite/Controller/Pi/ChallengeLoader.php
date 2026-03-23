<?php

namespace Ebizmarts\SagePaySuite\Controller\Pi;

use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;
use Magento\Framework\Filesystem\Driver\File;
use \Magento\Framework\Escaper;
use Ebizmarts\SagePaySuite\Model\AcsUrl;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;

class ChallengeLoader extends Action
{
    protected $resultRawFactory;
    protected $moduleDirReader;
    protected $fileDriver;
    protected $escaper;
    private $acsUrl;
    private $suiteLogger;
    private $config;

    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        ModuleDirReader $moduleDirReader,
        File $fileDriver,
        Escaper $escaper,
        AcsUrl $acsUrl,
        Logger $suiteLogger,
        Config $config
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->moduleDirReader = $moduleDirReader;
        $this->fileDriver = $fileDriver;
        $this->escaper = $escaper;
        $this->acsUrl = $acsUrl;
        $this->suiteLogger = $suiteLogger;
        $this->config = $config;
        $this->config->setMethodCode(Config::METHOD_PI);
    }

    private function loadTemplate($templatePath, $replacements = [])
    {
        $moduleViewDir = $this->moduleDirReader->getModuleDir(
            Dir::MODULE_VIEW_DIR,
            'Ebizmarts_SagePaySuite'
        );

        $filePath = $moduleViewDir . $templatePath;

        if (!$this->fileDriver->isExists($filePath)) {
            throw new \Magento\Framework\Exception\NotFoundException(__('Template file not found.'));
        }

        $content = $this->fileDriver->fileGetContents($filePath);

        foreach ($replacements as $placeholder => $value) {
            // Replace null values with an empty string and ensure the result of escapeHtml is not null
            $value = $value ?? '';
            $escapedValue = $this->escaper->escapeHtml($value, ENT_QUOTES, 'UTF-8') ?? '';
            $content = str_replace($placeholder, $escapedValue, $content);
        }

        return $content;
    }

    public function execute()
    {
        $challenge_url  = $this->getRequest()->getParam('challenge-url');
        $challenge_creq = $this->getRequest()->getParam('challenge-creq');

        try {
            if ($this->config->getValue("validate_acs_url") &&
                !$this->acsUrl->shouldAllowAcsUrl($challenge_url)
            ) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The provided challenge URL is not allowed: %1', $challenge_url)
                );
            }

            $htmlContent = $this->loadTemplate('/frontend/web/html/iframe_template.html', [
                '{CHALLENGE_URL}' => $challenge_url,
                '{CHALLENGE_CREQ}' => $challenge_creq
            ]);

            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setHeader('Content-Type', 'text/html', true);
            $resultRaw->setContents($htmlContent);

            return $resultRaw;
        } catch (\Exception $e) {
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $e->getMessage(), [__METHOD__, __LINE__]);

            $errorContent = $this->loadTemplate('/frontend/web/html/error_template.html', [
                '{ERROR_MESSAGE}' => __('Try again or contact store support.')
            ]);

            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setHeader('Content-Type', 'text/html', true);
            $resultRaw->setContents($errorContent);

            return $resultRaw;
        }
    }
}
