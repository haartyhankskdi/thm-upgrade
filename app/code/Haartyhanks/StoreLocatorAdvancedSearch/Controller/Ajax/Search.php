<?php
namespace Haartyhanks\StoreLocatorAdvancedSearch\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;

class Search extends Action
{
    protected $jsonFactory;
    protected $collectionFactory;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $query = $this->getRequest()->getParam('q');
        $result = $this->jsonFactory->create();

        if (!$query) {
            return $result->setData([]);
        }

        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter(
            ['name','city','state','zip'],
            [
                ['like' => "%$query%"],
                ['like' => "%$query%"],
                ['like' => "%$query%"],
                ['like' => "%$query%"]
            ]
        );

        $collection->setPageSize(8);

        $data = [];

        foreach ($collection as $store) {
            $data[] = [
                'id' => $store->getId(),
                'name' => $store->getName(),
                'address' => $store->getAddress(),
                'city' => $store->getCity(),
                'state' => $store->getState(),
                'lat' => $store->getLat(),
                'lng' => $store->getLng()
            ];
        }

        return $result->setData($data);
    }
}
