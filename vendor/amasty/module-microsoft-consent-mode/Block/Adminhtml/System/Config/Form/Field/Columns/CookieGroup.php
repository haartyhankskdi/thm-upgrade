<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Microsoft Consent Mode
 */

namespace Amasty\MicrosoftConsentMode\Block\Adminhtml\System\Config\Form\Field\Columns;

use Amasty\MicrosoftConsentMode\Model\OptionSource\CookieGroups;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class CookieGroup extends Select
{
    public function __construct(
        private readonly CookieGroups $cookieGroupsOptions,
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
            $this->setOptions($this->cookieGroupsOptions->toOptionArray());
        }

        return parent::_toHtml();
    }
}
