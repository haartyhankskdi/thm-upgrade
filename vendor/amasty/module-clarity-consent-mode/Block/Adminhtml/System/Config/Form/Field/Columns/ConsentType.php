<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Cookie Clarity Consent Mode
 */

namespace Amasty\ClarityConsentMode\Block\Adminhtml\System\Config\Form\Field\Columns;

use Magento\Framework\View\Element\AbstractBlock;

class ConsentType extends AbstractBlock
{
    protected function _toHtml(): string
    {
        $inputId = $this->getInputId();
        $inputName = $this->getInputName();
        $columnName = $this->getColumnName();
        $column = $this->getColumn();

        return '<input type="text" id="' . $inputId
            . '" name="' . $inputName
            . '" value="<%- ' . $columnName
            . ' %>" class="' . ($column['class'] ?? 'input-text')
            . '" <%- typeof disabled !== "undefined" ? "readonly" : "" %>/>';
    }
}
