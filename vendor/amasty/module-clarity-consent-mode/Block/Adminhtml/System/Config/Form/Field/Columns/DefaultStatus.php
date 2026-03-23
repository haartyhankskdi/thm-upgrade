<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Cookie Clarity Consent Mode
 */

namespace Amasty\ClarityConsentMode\Block\Adminhtml\System\Config\Form\Field\Columns;

use Amasty\ClarityConsentMode\Model\OptionSource\ConsentDefaultStatus;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class DefaultStatus extends Select
{
    public function __construct(
        private readonly ConsentDefaultStatus $consentDefaultStatusOptions,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
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
