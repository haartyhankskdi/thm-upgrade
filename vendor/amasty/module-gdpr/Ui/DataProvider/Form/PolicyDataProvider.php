<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Ui\DataProvider\Form;

use Amasty\Gdpr\Model\Policy;
use Amasty\Gdpr\Model\PolicyContent;
use Amasty\Gdpr\Model\ResourceModel\Policy\Collection;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Amasty\Gdpr\Model\ResourceModel\PolicyContent\Collection as ContentCollection;
use Amasty\Gdpr\Model\PolicyRepository;

class PolicyDataProvider extends AbstractDataProvider
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
     * @var ContentCollection
     */
    private $contentCollection;

    /**
     * @var PolicyRepository
     */
    private $policyRepository;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        DataPersistorInterface $dataPersistor,
        RequestInterface $request,
        UrlInterface $url,
        ContentCollection $contentCollection,
        PolicyRepository $policyRepository,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collection;
        $this->dataPersistor = $dataPersistor;
        $this->request = $request;
        $this->url = $url;
        $this->contentCollection = $contentCollection;
        $this->policyRepository = $policyRepository;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $data = parent::getData();

        if ($data['totalRecords'] > 0) {
            $id = (int)$data['items'][0]['id'];
            $data[$id]['policy'] = $data['items'][0];

            if ($content = $this->getStoreContent()) {
                $data[$id]['policy']['content'] = $content;
            }
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
        $this->data['config']['submit_url'] = $this->url->getUrl('*/*/save', ['_current' => true]);
        $meta = parent::getMeta();
        $config = [
            'scopeLabel' => __('[STORE VIEW]')
        ];

        if ($this->request->getParam('store')) {
            $config['service'] = [
                'template' => 'ui/form/element/helper/service',
            ];

            $config['disabled'] = !$this->getStoreContent();
        }
        $meta['general']['children']['content']['arguments']['data']['config'] = $config;

        if ($this->request->getParam('store')) {
            $meta['general']['children']['policy_version']['arguments']['data']['config']['disabled'] = true;
            $meta['general']['children']['comment']['arguments']['data']['config']['disabled'] = true;
        }
        $id = $this->request->getParam('id');

        if (!$id) {
            return $meta;
        }
        $policy = $this->policyRepository->getById($id);

        if (!$policy || $policy->getStatus() != Policy::STATUS_DRAFT) {
            $meta['general']['children']['policy_version']['arguments']['data']['config']['disabled'] = true;
            $meta['general']['children']['comment']['arguments']['data']['config']['disabled'] = true;
            $meta['general']['children']['content']['arguments']['data']['config']['disabled'] = true;
            $meta['general']['children']['content']['arguments']['data']['config']['wysiwyg'] = false;
            $meta['general']['children']['content']['arguments']['data']['config']['formElement'] = 'textarea';
            unset($meta['general']['children']['content']['arguments']['data']['config']['service']);
        }

        return $meta;
    }

    /**
     * @return bool|mixed
     */
    protected function getStoreContent()
    {
        $storeId = $this->request->getParam('store');
        $policyId = $this->request->getParam($this->getRequestFieldName());

        if (!$storeId || !$policyId) {
            return false;
        }

        /** @var PolicyContent $content */
        $content = $this->contentCollection->findByStoreAndPolicy($policyId, $storeId);

        if ($content->getId()) {
            return $content->getContent();
        } else {
            return false;
        }
    }
}
