<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Block\Adminhtml\Policy\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Amasty\Gdpr\Model\Policy;

class SaveAndContinueButton extends AbstractButton implements ButtonProviderInterface
{
    /**
     * @return array|bool
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getButtonData()
    {
        $policy = $this->getPolicy();
        if ($policy && $policy->getStatus() == Policy::STATUS_ENABLED) {
            return false;
        }

        return [
            'label' => __('Save and Continue Edit'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'button' => [
                        'event' => 'saveAndContinueEdit'
                    ],
                ],
            ],
            'sort_order' => 80,
        ];
    }
}
