<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Microsoft Consent Mode
 */

namespace Amasty\MicrosoftConsentMode\Block\Adminhtml\System\Config\Form\Field;

use Amasty\MicrosoftConsentMode\Model\Cookie\CookieGroupManager;
use Amasty\MicrosoftConsentMode\Block\Adminhtml\System\Config\Form\Field\Columns\ConsentType;
use Amasty\MicrosoftConsentMode\Block\Adminhtml\System\Config\Form\Field\Columns\DefaultStatus;
use Amasty\MicrosoftConsentMode\Block\Adminhtml\System\Config\Form\Field\Columns\CookieGroup;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\BlockInterface;

class ConsentTypes extends AbstractFieldArray
{
    /**
     * @var array
     */
    private array $defaultConsentModes = [
        'ad_storage'
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
            ['label' => __('Consent Type'), 'renderer' => $this->getRenderer(ConsentType::class)]
        );
        $this->addColumn(
            'default_status',
            ['label' => __('Default Status'), 'renderer' => $this->getRenderer(DefaultStatus::class)]
        );
        $this->addColumn(
            'cookie_group',
            ['label' => __('Cookie Group'), 'renderer' => $this->getRenderer(CookieGroup::class)]
        );
        $this->_addAfter = false;
    }

    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $defaultStatus = $row->getDefaultStatus();
        if ($defaultStatus !== null) {
            $options['option_' . $this->getRenderer(DefaultStatus::class)->calcOptionHash($defaultStatus)] =
                'selected="selected"';
        }

        $cookieGroup = $this->cookieGroupManager->getMicrosoftGroupId();
        if ($cookieGroup !== null) {
            $options['option_' . $this->getRenderer(CookieGroup::class)->calcOptionHash($cookieGroup)] =
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
