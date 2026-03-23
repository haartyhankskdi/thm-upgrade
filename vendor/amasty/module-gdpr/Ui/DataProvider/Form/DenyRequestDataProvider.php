<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Ui\DataProvider\Form;

use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\Collection;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\Component\MassAction\Filter;

class DenyRequestDataProvider extends AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        Filter $filter,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collection;
        $this->dataPersistor = $dataPersistor;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get data
     *
     * @return array
     * @throws LocalizedException
     */
    public function getData()
    {
        $this->filter->applySelectionOnTargetProvider(); // compatibility with Mass Actions on Magento 2.1.0
        /** @var Collection $collection */
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        $data = parent::getData();

        $data[null]['ids'] = implode(',', $collection->getColumnValues('id'));

        if ($savedData = $this->dataPersistor->get('formData')) {
            $data = $savedData;

            $this->dataPersistor->clear('formData');
        }

        return $data;
    }
}
