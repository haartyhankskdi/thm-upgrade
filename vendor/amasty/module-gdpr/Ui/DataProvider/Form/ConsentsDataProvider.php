<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Ui\DataProvider\Form;

use Amasty\Gdpr\Model\Consent\Consent;
use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStore;
use Amasty\Gdpr\Model\Consent\Repository;
use Amasty\Gdpr\Model\Consent\ResourceModel\Collection;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Ui\DataProvider\AbstractDataProvider;
use \Amasty\Gdpr\Model\Consent\ConsentStore\ResourceModel\ConsentStoreCollectionFactory;

class ConsentsDataProvider extends AbstractDataProvider
{
    public const CONSENT_SCOPE = 'consent';

    /**
     * @var RequestInterface
     */
    private $request;

    public const INVISIBLE_FOR_STORE_FIELDS = [
        Consent::CONSENT_NAME,
        Consent::CONSENT_CODE
    ];

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var array
     */
    private $collectedData;

    /**
     * @var ConsentStoreCollectionFactory
     */
    private $consentStoreCollectionFactory;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        Repository $repository,
        ConsentStoreCollectionFactory $consentStoreCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->request = $request;
        $this->repository = $repository;
        $this->consentStoreCollectionFactory = $consentStoreCollectionFactory;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData()
    {
        $storeId = (int)$this->request->getParam('store', Store::DEFAULT_STORE_ID);
        $consentId = (int)$this->request->getParam('id');

        if (!$consentId) {
            return [];
        }

        return $this->collectData($consentId, $storeId);
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     *
     * @return mixed|void
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        return null; //suppress parent method call
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getMeta()
    {
        $storeId = (int)$this->request->getParam('store', Store::DEFAULT_STORE_ID);
        $consentId = (int)$this->request->getParam('id');
        $meta = parent::getMeta();
        $preparedMeta = $this->collectData($consentId, $storeId)['meta'] ?? [];

        return array_merge_recursive(
            $meta,
            $preparedMeta
        );
    }

    public function _resetState(): void
    {
        $this->collectedData = null;
    }

    /**
     * @param int $consentId
     * @param int $storeId
     *
     * @return array
     * @throws NoSuchEntityException
     */
    private function collectData(int $consentId, int $storeId)
    {
        if (!$this->collectedData) {
            $this->collectedData = $storeId === Store::DEFAULT_STORE_ID ?
                $this->getConsentDataForDefaultStore($consentId, $storeId) :
                $this->getConsentDataForNonDefaultStore($consentId, $storeId);
        }

        return $this->collectedData;
    }

    /**
     * @param int $consentId
     * @param int $storeId
     *
     * @return array
     */
    private function getConsentDataForDefaultStore(int $consentId, int $storeId)
    {
        try {
            $consent = $this->repository->getById($consentId, $storeId);
        } catch (NoSuchEntityException $e) {
            return [];
        }

        $data[$consentId][self::CONSENT_SCOPE] = array_merge(
            $consent->getData(),
            $consent->getStoreModel()->getData()
        );

        return $data;
    }

    /**
     * @param int $consentId
     * @param int $storeId
     *
     * @return array
     * @throws NoSuchEntityException
     */
    private function getConsentDataForNonDefaultStore(int $consentId, int $storeId)
    {
        $defaultStoreConsent = $this->repository->getById($consentId, Store::DEFAULT_STORE_ID);
        $metaFields = array_flip(Collection::NULLABLE_FIELDS);
        $collection = $this->consentStoreCollectionFactory
            ->create()
            ->addFieldToFilter(
                ConsentStore::CONSENT_ENTITY_ID,
                $consentId
            )->addFieldToFilter(
                ConsentStore::CONSENT_STORE_ID,
                $storeId
            );
        $storeModel = $collection->getFirstItem();

        foreach ($defaultStoreConsent->getStoreModel()->getData() as $key => $value) {
            $notHasData = $storeModel->getData($key) === null;

            if ($notHasData) {
                $storeModel->setData($key, $value);
            }

            if (isset($metaFields[$key])) {
                $metaFields[$key] = $notHasData;
            }
        }

        $storeModelDoesNotExists = !$collection->count();

        if ($storeModelDoesNotExists) {
            $storeModel->unsetData(ConsentStore::ID);
        }

        $data[$consentId][self::CONSENT_SCOPE] = array_merge(
            $defaultStoreConsent->getData(),
            $storeModel->getData(),
            ['store_id' => $storeId]
        );
        $data['meta'] = $this->prepareMeta($metaFields);

        return $data;
    }

    /**
     * @param array $nonexistentFields
     *
     * @return array
     */
    private function prepareMeta(array $nonexistentFields)
    {
        $meta = [];
        $config = [
            'scopeLabel' => __('[STORE VIEW]'),
            'service' => [
                'template' => 'ui/form/element/helper/service'
            ]
        ];

        foreach ($nonexistentFields as $field => $value) {
            $config['disabled'] = $value;
            $meta['general']['children'][$field]['arguments']['data']['config'] = $config;
        }

        foreach (self::INVISIBLE_FOR_STORE_FIELDS as $field) {
            $meta['general']['children'][$field]['arguments']['data']['config'] = ['visible' => false];
        }

        return $meta;
    }
}
