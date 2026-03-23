<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Cookie Clarity Consent Mode
 */

namespace Amasty\ClarityConsentMode\Block\Adminhtml\System\Config\Form\Field;

use Amasty\ClarityConsentMode\Model\Cookie\CookieGroupManager;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\BlockInterface;

class ConsentTypes extends AbstractFieldArray
{
    public const DEFAULT_CONFIG_CLARITY_GROUP = 'clarity';

    /**
     * @var array
     */
    private array $defaultConsentModes = [
        'ad_Storage',
        'analytics_Storage'
    ];

    /**
     * @var BlockInterface[]
     */
    private array $renderersStorage = [];

    /**
     * @var CookieGroupManager $cookieGroupManager
     */
    private CookieGroupManager $cookieGroupManager;

    public function __construct(
        Context $context,
        CookieGroupManager $cookieGroupManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cookieGroupManager = $cookieGroupManager;
    }

    protected function _prepareToRender(): void
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
        /* check default value */
        if ($cookieGroup === self::DEFAULT_CONFIG_CLARITY_GROUP) {
            $cookieGroup = $this->cookieGroupManager->getClarityGroupId();
        }

        if ($cookieGroup !== null) {
            $options['option_' . $this->getRenderer(Columns\CookieGroup::class)->calcOptionHash($cookieGroup)] =
                'selected="selected"';

            if ($row->getCookieGroup() === self::DEFAULT_CONFIG_CLARITY_GROUP) {
                $columnValues = $row->getData('column_values');
                $columnValues[$row->getData('_id') . '_cookie_group'] = $cookieGroup;

                $row->setData('column_values', $columnValues);
                $row->setData('cookie_group', $cookieGroup);
            }
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

    protected function _toHtml(): string
    {
        $html = parent::_toHtml();

        return str_replace(
            'class="action-delete"',
            'class="action-delete" <%- typeof disabled !== "undefined" ? disabled : "" %>',
            $html
        );
    }
}
