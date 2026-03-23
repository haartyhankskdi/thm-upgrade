<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Google Consent Mode
 */

namespace Amasty\GoogleConsentMode\Block\Adminhtml\System\Config\Form\Field\Columns;

use Amasty\GoogleConsentMode\Model\OptionSource\CookieGroups;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class CookieGroup extends Select
{
    /**
     * @var CookieGroups
     */
    private $cookieGroupsOptions;

    public function __construct(
        Context $context,
        CookieGroups $cookieGroupsOptions,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cookieGroupsOptions = $cookieGroupsOptions;
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
