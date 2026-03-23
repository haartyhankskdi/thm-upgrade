<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Consent;

use Amasty\Gdpr\Api\ConsentRepositoryInterface;
use Amasty\Gdpr\Api\Data\ConsentInterface;
use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStore;
use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStoreFactory;
use Amasty\Gdpr\Model\Consent\ConsentStore\ResourceModel\ConsentStore as ConsentStoreResource;
use Amasty\Gdpr\Model\Consent\ConsentStore\ResourceModel\ConsentStoreCollection;
use Amasty\Gdpr\Model\Consent\ConsentStore\ResourceModel\ConsentStoreCollectionFactory;
use Amasty\Gdpr\Model\Consent\ResourceModel\Consent as ConsentResource;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;

class Repository implements ConsentRepositoryInterface
{
    /**
     * @var ConsentStoreFactory
     */
    private $consentStoreFactory;

    /**
     * @var ConsentFactory
     */
    private $consentFactory;

    /**
     * @var ConsentResource
     */
    private $consentResource;

    /**
     * @var array
     */
    private $consents = [];

    /**
     * @var ConsentStoreCollectionFactory
     */
    private $consentStoreCollectionFactory;

    /**
     * @var ConsentStoreResource
     */
    private $consentStoreResource;

    public function __construct(
        ConsentStoreFactory $consentStoreFactory,
        ConsentFactory $consentFactory,
        ConsentResource $consentResource,
        ConsentStoreCollectionFactory $consentStoreCollectionFactory,
        ConsentStoreResource $consentStoreResource
    ) {
        $this->consentStoreFactory = $consentStoreFactory;
        $this->consentFactory = $consentFactory;
        $this->consentResource = $consentResource;
        $this->consentStoreCollectionFactory = $consentStoreCollectionFactory;
        $this->consentStoreResource = $consentStoreResource;
    }

    /**
     * @param int $consentId
     * @param int $storeId
     *
     * @return ConsentInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $consentId, int $storeId = Store::DEFAULT_STORE_ID)
    {
        if (!isset($this->consents[$consentId][$storeId])) {
            $consent = $this->getEmptyConsentModel();
            $this->consentResource->load($consent, $consentId);

            if (!$consent->getId()) {
                throw new NoSuchEntityException(__('Consent with specified ID "%1" not found.', $consentId));
            }

            $this->loadStoreModel($consent, $storeId);

            $this->consents[$consentId][$storeId] = $consent;
        }

        return $this->consents[$consentId][$storeId];
    }

    /**
     * @param ConsentInterface $consent
     * @param int $storeId
     */
    private function loadNonDefaultStoreModel(ConsentInterface $consent, int $storeId)
    {
        /** @var ConsentStoreCollection $consentStoreCollection * */
        $consentStoreCollection = $this->consentStoreCollectionFactory->create();
        $consentStoreCollection->addFieldToFilter(
            ConsentStore::CONSENT_ENTITY_ID,
            $consent->getId()
        )->addFieldToFilter(
            ConsentStore::CONSENT_STORE_ID,
            ['in' => [$storeId, Store::DEFAULT_STORE_ID]]
        )->addOrder(
            ConsentStore::CONSENT_STORE_ID,
            Collection::SORT_ORDER_DESC
        );
        $consentStore = $this->getEmptyConsentStoreModel();

        foreach ($consentStoreCollection->getData() as $item) {
            foreach ($item as $key => $value) {
                if (isset($item[$key])
                    && !$consentStore->hasData($key)
                ) {
                    $consentStore->setData($key, $value);
                }
            }
        }

        if ($consentStoreCollection->count() === 1) {
            $consentStore->unsetData(ConsentStore::ID);
        }

        $consent->setStoreModel($consentStore);
    }

    /**
     * @param string $consentCode
     *
     * @param int $storeId
     *
     * @return ConsentInterface
     * @throws NoSuchEntityException
     */
    public function getByConsentCode(string $consentCode, int $storeId = Store::DEFAULT_STORE_ID)
    {
        $consent = $this->getEmptyConsentModel();
        $this->consentResource->load($consent, $consentCode, Consent::CONSENT_CODE);

        if (!$consent->getId()) {
            throw new NoSuchEntityException(__('Consent with consent code %1 not found', $consentCode));
        }

        $this->loadStoreModel($consent, $storeId);

        return $consent;
    }

    /**
     * @param int $consentId
     *
     * @return bool|void
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $consentId)
    {
        $consent = $this->getById($consentId);

        return $this->delete($consent);
    }

    /**
     * @param ConsentInterface $consent
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ConsentInterface $consent)
    {
        if (!$consent->getConsentId()) {
            throw new CouldNotDeleteException(__('Unable to remove model without id'));
        }

        try {
            $this->consentResource->delete($consent);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to remove consent, message is %1', $e->getMessage()));
        }

        unset($this->consents[$consent->getConsentId()]);

        return true;
    }

    /**
     * @param ConsentInterface $consent
     *
     * @throws CouldNotSaveException
     */
    public function save(ConsentInterface $consent)
    {
        try {
            $storeModel = $consent->getStoreModel();

            if ($consent->getConsentId()) {
                $consent = $this->getById((int)$consent->getConsentId())->addData($consent->getData());
            } else {
                $tempConsent = $this->getEmptyConsentModel();
                $this->consentResource->load(
                    $tempConsent,
                    $consent->getConsentCode(),
                    Consent::CONSENT_CODE
                );
                $consent = $tempConsent->getId() ? $tempConsent->addData($consent->getData()) : $consent;
            }

            $this->consentResource->save($consent);

            if ($storeModel->getId()) {
                $emptyStoreModel = $this->getEmptyConsentStoreModel();
                $this->consentStoreResource->load($emptyStoreModel, $storeModel->getId());
                $storeModel = $emptyStoreModel->addData($storeModel->getData());
            }

            if ($consent->isHideTheConsentAfterUserLeftTheConsent()) {
                $storeModel->setData(ConsentStore::LOG_THE_CONSENT, true);
            }
            $storeModel->setData(ConsentStore::CONSENT_ENTITY_ID, (int)$consent->getId());
            $this->consentStoreResource->save($storeModel);
            $consent->setStoreModel($storeModel);
        } catch (\Exception $e) {
            if ($consent->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save consent with ID %1. Error: %2',
                        [$consent->getId(), $e->getMessage()]
                    )
                );
            }

            throw new CouldNotSaveException(__('Unable to save new consent. Error: %1', $e->getMessage()));
        }
    }

    /**
     * @return Consent
     */
    public function getEmptyConsentModel()
    {
        return $this->consentFactory->create();
    }

    /**
     * @return ConsentStore
     */
    public function getEmptyConsentStoreModel()
    {
        return $this->consentStoreFactory->create();
    }

    /**
     * @param Consent $consent
     * @param int $storeId
     *
     * @throws NoSuchEntityException
     */
    private function loadDefaultStoreModel(Consent $consent, int $storeId)
    {
        /** @var ConsentStoreCollection $consentStoreCollection * */
        $consentStoreCollection = $this->consentStoreCollectionFactory->create();
        $consentStoreCollection->addFieldToFilter(
            ConsentStore::CONSENT_ENTITY_ID,
            $consent->getConsentId()
        )->addFieldToFilter(
            ConsentStore::CONSENT_STORE_ID,
            $storeId
        );

        if (!$consentStoreCollection->count()) {
            throw new NoSuchEntityException(
                __(
                    'Data consistency is broken. '
                    . 'There is no entity by default store for a consent with ID %1',
                    $consent->getId()
                )
            );
        }

        $consent->setStoreModel($consentStoreCollection->getFirstItem());
    }

    /**
     * @param $consent
     * @param int $storeId
     *
     * @throws NoSuchEntityException
     */
    private function loadStoreModel($consent, int $storeId)
    {
        if ($storeId === Store::DEFAULT_STORE_ID) {
            $this->loadDefaultStoreModel($consent, $storeId);
        } else {
            $this->loadNonDefaultStoreModel($consent, $storeId);
        }
    }
}
