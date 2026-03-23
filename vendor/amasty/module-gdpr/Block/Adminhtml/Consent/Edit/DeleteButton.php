<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Block\Adminhtml\Consent\Edit;

use Amasty\Gdpr\Api\Data\ConsentInterface;
use Amasty\Gdpr\Model\Consent\Consent;
use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStore;
use Amasty\Gdpr\Model\Consent\Repository;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Store\Model\Store;

class DeleteButton implements ButtonProviderInterface
{
    /**
     * @var Repository
     */
    private $consentRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        Repository $consentRepository,
        RequestInterface $request,
        UrlInterface $urlBuilder
    ) {
        $this->consentRepository = $consentRepository;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $consent = $this->getConsent();

        if ($consent) {
            $alertMessage = __('Are you sure you want to do this?');
            $onClick = sprintf(
                'deleteConfirm("%s", "%s")',
                $alertMessage,
                $this->urlBuilder->getUrl("*/*/delete", [
                    Consent::ID => $consent->getId(),
                    ConsentStore::CONSENT_STORE_ID => $consent->getStoreId()
                ])
            );

            return [
                'class' => 'delete',
                'id' => 'consent-edit-delete-button',
                'on_click' => $onClick,
                'sort_order' => 20,
                'label' => __('Delete Consent')
            ];
        }

        return [];
    }

    /**
     * @return ConsentInterface|null
     */
    protected function getConsent()
    {
        $storeId = (int)$this->request->getParam('store', Store::DEFAULT_STORE_ID);
        $consentId = (int)$this->request->getParam('id');

        if ($consentId) {
            try {
                return $this->consentRepository->getById($consentId, $storeId);
            } catch (NoSuchEntityException $e) {
                return null;
            }
        }

        return null;
    }
}
