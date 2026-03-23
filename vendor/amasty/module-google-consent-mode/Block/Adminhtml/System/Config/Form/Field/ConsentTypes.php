<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Google Consent Mode
 */

namespace Amasty\GoogleConsentMode\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\BlockInterface;

class ConsentTypes extends AbstractFieldArray
{
    /**
     * @var array
     */
    private $defaultConsentModes = [
        'ad_storage',
        'analytics_storage',
        'ad_user_data',
        'ad_personalization'
    ];

    /**
     * @var BlockInterface[]
     */
    private $renderersStorage = [];

    protected function _prepareToRender()
    {
        $this->addColumn(
            'consent_type',
            ['label' => __('Consent Type'), 'renderer' => $this->getRenderer(Columns\ConsentType::class)]
        );
        $this->addColumn(
            'default_status',
            ['label' => __('Default Status'), 'renderer' => $this->getRenderer(Columns\DefaultStatus::class)]
        );
        $this->addColumn(
            'cookie_group',
            ['label' => __('Cookie Group'), 'renderer' => $this->getRenderer(Columns\CookieGroup::class)]
        );
        $this->_addAfter = false;
    }

    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $defaultStatus = $row->getDefaultStatus();
        if ($defaultStatus !== null) {
            $options['option_' . $this->getRenderer(Columns\DefaultStatus::class)->calcOptionHash($defaultStatus)] =
                'selected="selected"';
        }

        $cookieGroup = $row->getCookieGroup();
        if ($cookieGroup !== null) {
            $options['option_' . $this->getRenderer(Columns\CookieGroup::class)->calcOptionHash($cookieGroup)] =
                'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);

        if (in_array($row->getConsentType(), $this->defaultConsentModes)) {
            $row->setData('disabled', 'disabled');
        }
    }

    private function getRenderer(string $class): BlockInterface
    {
        if (!isset($this->renderersStorage[$class])) {
            $this->renderersStorage[$class] = $this->getLayout()->createBlock(
                $class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->renderersStorage[$class];
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();

        return str_replace(
            'class="action-delete"',
            'class="action-delete" <%- typeof disabled !== "undefined" ? disabled : "" %>',
            $html
        );
    }
}
