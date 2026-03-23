<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Google Consent Mode
 */

namespace Amasty\GoogleConsentMode\Block\Adminhtml\System\Config\Form\Field\Columns;

use Amasty\GoogleConsentMode\Model\OptionSource\ConsentDefaultStatus;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class DefaultStatus extends Select
{
    /**
     * @var ConsentDefaultStatus
     */
    private $consentDefaultStatusOptions;

    public function __construct(
        Context $context,
        ConsentDefaultStatus $consentDefaultStatusOptions,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->consentDefaultStatusOptions = $consentDefaultStatusOptions;
    }

    public function setInputName(string $value): self
    {
        return $this->setName($value);
    }

    public function setInputId(string $value): self
    {
        return $this->setId($value);
    }

    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->consentDefaultStatusOptions->toOptionArray());
        }

        return parent::_toHtml();
    }
}
