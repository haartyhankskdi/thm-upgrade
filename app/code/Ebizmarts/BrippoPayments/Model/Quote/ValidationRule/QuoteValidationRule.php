<?php
declare(strict_types=1);

namespace Ebizmarts\BrippoPayments\Model\Quote\ValidationRule;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\PaymentMethods\PaymentMethod as PaymentMethodsHelper;
use Ebizmarts\BrippoPayments\Model\Express;
use Ebizmarts\BrippoPayments\Model\PayByLink;
use Ebizmarts\BrippoPayments\Model\PaymentElement;
use Exception;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ValidationRules\QuoteValidationRuleInterface;

class QuoteValidationRule implements QuoteValidationRuleInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /** @var Logger */
    private $logger;

    /** @var PaymentMethodsHelper */
    private $paymentMethodsHelper;

    /**
     * PaymentElementValidationRule constructor.
     * @param ValidationResultFactory $validationResultFactory
     * @param Logger $logger
     * @param PaymentMethodsHelper $paymentMethodsHelper
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        Logger $logger,
        PaymentMethodsHelper $paymentMethodsHelper
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->logger = $logger;
        $this->paymentMethodsHelper = $paymentMethodsHelper;
    }

    /**
     * @inheritdoc
     *
     * @param Quote $quote
     * @return array
     */
    public function validate(Quote $quote): array
    {
        $validationErrors = [];

        try {
            if ($quote->getPayment()->getMethod() === PaymentElement::METHOD_CODE
            || $quote->getPayment()->getMethod() === Express::METHOD_CODE
            || $quote->getPayment()->getMethod() === PayByLink::METHOD_CODE
            ) {
                $hash = $quote->getBrippoQuoteHash();

                if (empty($hash) || !$this->paymentMethodsHelper->isQuoteHashValid($quote)) {
                    $validationErrors[] = __(
                        'Unexpected error. Please refresh the page and try again.'
                    );
                    $this->logger->log("QuoteValidationRule: invalid quote, hash is: ". $hash);
                }
            }
        } catch (Exception $e) {
            $this->logger->log("Couldn't validate quote, QuoteValidationRule error: " . $e->getMessage());
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }
}
