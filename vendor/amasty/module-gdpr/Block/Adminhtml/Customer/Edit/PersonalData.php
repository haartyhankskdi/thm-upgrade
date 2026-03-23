<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Block\Adminhtml\Customer\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class PersonalData implements ButtonProviderInterface
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    public function __construct(
        Context $context,
        Registry $registry
    ) {
        $this->authorization = $context->getAuthorization();
        $this->urlBuilder = $context->getUrlBuilder();
        $this->registry = $registry;
    }

    public function getButtonData(): array
    {
        if (!$this->getCustomerId() || !$this->authorization->isAllowed('Amasty_Gdpr::personal_data')) {
            return [];
        }

        return [
            'label' => __('Personal Data'),
            'class' => 'abs-action-quaternary',
            'button_class' => 'abs-action-quaternary ',
            'class_name' => \Amasty\Gdpr\Ui\Component\Control\RegularSplitButton::class,
            'options' => [
                [
                    'label' => __('Anonymise'),
                    'onclick' => sprintf(
                        'confirmSetLocation("%s", "%s")',
                        __('Are you sure you want to anonymise personal data?'),
                        $this->getAnonymiseUrl()
                    ),
                ],
                [
                    'label' => __('Download'),
                    'onclick' => sprintf('setLocation("%s")', $this->getDownloadUrl()),
                ],
                [
                    'label' => __('Delete'),
                    'onclick' => sprintf(
                        'confirmSetLocation("%s", "%s")',
                        __('Are you sure you want to delete personal data? (Personal data in previously created '
                            . 'orders, invoices, shipments and credit memos will be anonymised then.)'),
                        $this->getDeleteUrl()
                    ),
                ]
            ],
            'sort_order' => 75,
        ];
    }

    public function getAnonymiseUrl(): string
    {
        return $this->urlBuilder->getUrl(
            'amasty_gdpr/customer/anonymise',
            ['customerId' => $this->getCustomerId(), '_nosid' => true,]
        );
    }

    public function getDownloadUrl(): string
    {
        return $this->urlBuilder->getUrl(
            'amasty_gdpr/customer/downloadCsv',
            ['customerId' => $this->getCustomerId(), '_nosid' => true,]
        );
    }

    public function getDeleteUrl(): string
    {
        return $this->urlBuilder->getUrl(
            'amasty_gdpr/customer/anonymiseAndDeleteData',
            ['customerId' => $this->getCustomerId(), '_nosid' => true,]
        );
    }

    /**
     * Return the customer Id.
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        $customerId = $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);

        return $customerId;
    }
}
