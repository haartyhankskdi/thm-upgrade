<?php
declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Model\Quote\ValidationRule;

use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ValidationRules\QuoteValidationRuleInterface;

class QuoteValidationRule implements QuoteValidationRuleInterface
{
    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        Data $dataHelper,
        Logger $logger,
        ValidationResultFactory $validationResultFactory,
        Config $config
    ) {
        $this->dataHelper              = $dataHelper;
        $this->logger                  = $logger;
        $this->validationResultFactory = $validationResultFactory;
        $this->config                  = $config;
    }

    public function validate(Quote $quote): array
    {
        $validationErrors = [];

        try {
            if (($quote->getPayment()->getMethod() === Config::METHOD_FORM
                || $quote->getPayment()->getMethod() === Config::METHOD_PI
                || $quote->getPayment()->getMethod() === Config::METHOD_SERVER
                || $quote->getPayment()->getMethod() === Config::METHOD_PAYPAL)
                && $this->config->getAdvancedValue("quote_validator")
            ) {
                $hash = (string)$quote->getSagePayQuoteHash();

                if (empty($hash) || !$this->dataHelper->isValidQuoteHash($quote)) {
                    $validationErrors[] = __(
                        'Can\'t make payments, missing data'
                    );
                    $this->logger->sageLog(
                        Logger::LOG_EXCEPTION,
                        "QuoteValidationRule: invalid quote, hash is ".$hash,
                        [__METHOD__, __LINE__]
                    );
                }
            }
        } catch (\Exception $e) {
            $this->logger->sageLog(
                Logger::LOG_EXCEPTION,
                "Couldn't validate quote, QuoteValidationRule error ".$e->getMessage(),
                [__METHOD__, __LINE__]
            );
        }
        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }
}
