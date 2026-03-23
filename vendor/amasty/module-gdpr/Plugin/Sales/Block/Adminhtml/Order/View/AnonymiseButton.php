<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Plugin\Sales\Block\Adminhtml\Order\View;

use Amasty\Gdpr\Controller\Adminhtml\Order\Anonymise;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\View;

class AnonymiseButton
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    public function __construct(
        Context $context,
        Registry $registry
    ) {
        $this->authorization = $context->getAuthorization();
        $this->urlBuilder = $context->getUrlBuilder();
        $this->registry = $registry;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetLayout(View $subject, LayoutInterface $layout)
    {
        if ($this->authorization->isAllowed(Anonymise::ADMIN_RESOURCE) && $this->getCustomerId() === 0) {
            $subject->addButton('amgdpr-personal-data', [
                'label' => __('Anonymise'),
                'class' => 'abs-action-quaternary',
                'id' => 'amgdpr-personal-data',
                'onclick' => sprintf(
                    'confirmSetLocation("%s", "%s")',
                    __('Are you sure you want to anonymise personal data?'),
                    $this->getAnonymiseUrl()
                )
            ]);
        }
    }

    private function getAnonymiseUrl(): string
    {
        return $this->urlBuilder->getUrl(
            'amasty_gdpr/order/anonymise',
            ['increment_id' => $this->getOrderIncrementId(), '_nosid' => true]
        );
    }

    private function getOrderIncrementId(): ?string
    {
        return $this->registry->registry('current_order')->getIncrementId();
    }

    private function getCustomerId(): int
    {
        return (int)$this->registry->registry('current_order')->getCustomerId();
    }
}
