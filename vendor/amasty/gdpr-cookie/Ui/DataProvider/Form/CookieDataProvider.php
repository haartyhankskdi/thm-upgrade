<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Ui\DataProvider\Form;

use Amasty\GdprCookie\Api\CookieRepositoryInterface;
use Amasty\GdprCookie\Model\ResourceModel\Cookie\Collection;
use Amasty\GdprCookie\Model\StoreData\ScopedFieldsProvider;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class CookieDataProvider extends AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var CookieRepositoryInterface
     */
    private $cookieRepository;

    /**
     * @var ScopedFieldsProvider
     */
    private $scopedFieldsProvider;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        CookieRepositoryInterface $cookieRepository,
        ScopedFieldsProvider $scopedFieldsProvider,
        DataPersistorInterface $dataPersistor,
        RequestInterface $request,
        UrlInterface $url,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
        $this->dataPersistor = $dataPersistor;
        $this->request = $request;
        $this->url = $url;
        $this->cookieRepository = $cookieRepository;
        $this->scopedFieldsProvider = $scopedFieldsProvider;
    }

    public function getData()
    {
        $storeId = (int)$this->request->getParam('store');
        $data = parent::getData();

        if ($data['totalRecords'] > 0) {
            $cookieId = (int)$data['items'][0]['id'];
            $cookie = $this->cookieRepository->getById($cookieId, $storeId);
            $data[$cookieId]['cookie'] = $cookie->getData();
        }

        if ($savedData = $this->dataPersistor->get('formData')) {
            $id = isset($savedData['id']) ? $savedData['id'] : null;
            if (isset($data[$id])) {
                $data[$id] = array_merge($data[$id], $savedData);
            } else {
                $data[$id] = $savedData;
            }
            $this->dataPersistor->clear('formData');
        }

        return $data;
    }

    public function getMeta()
    {
        $storeId = (int)$this->request->getParam('store');
        $cookieId = (int)$this->request->getParam($this->getRequestFieldName());
        $this->data['config']['submit_url'] = $this->url->getUrl('*/*/save', ['_current' => true]);
        $meta = parent::getMeta();

        if (!$cookieId) {
            return $meta;
        }

        $cookie = $this->cookieRepository->getById($cookieId, $storeId);
        $storeEntityTable = $this->collection->getMainTable();

        foreach ($this->scopedFieldsProvider->getScopedFields($storeEntityTable) as $scopedField) {
            $meta['settings']['children'][$scopedField]['arguments']['data']['config'] = [
                'scopeLabel' => __('[STORE VIEW]')
            ];

            if ($storeId) {
                $meta['settings']['children'][$scopedField]['arguments']['data']['config']['service'] = [
                    'template' => 'ui/form/element/helper/service'
                ];
                $meta['settings']['children'][$scopedField]['arguments']['data']['config']['disabled'] =
                    $cookie->dataHasChangedFor($scopedField) === false;
            }
        }

        if ($cookieId && $storeId) {
            $meta['settings']['children']['name']['arguments']['data']['config']['disabled'] = true;
            $meta['settings']['children']['provider']['arguments']['data']['config']['disabled'] = true;
            $meta['settings']['children']['type']['arguments']['data']['config']['disabled'] = true;
        }

        return $meta;
    }
}
