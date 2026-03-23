<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Ui\DataProvider\Listing;

use Amasty\GdprCookie\Api\Data\CookieInterface;
use Amasty\GdprCookie\Model\ResourceModel\Cookie\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class CookieDataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collectionFactory = $collectionFactory;
    }

    public function getCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->collectionFactory->create()->joinGroup();
        }

        return $this->collection;
    }

    public function addOrder($field, $direction)
    {
        if ($field === "group") {
            $field = "COALESCE(groups.name, \"None\")";
        }
        parent::addOrder($field, $direction);
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        switch ($filter->getField()) {
            case 'id':
                $field = 'main_table.id';
                break;
            case 'name':
                $field = 'main_table.name';
                break;
            case 'group':
                $field = 'groups.id';
                break;
        }
        if ($filter->getValue() === "0" && $filter->getField() === "group") {
            $this->getCollection()->addFieldToFilter(CookieInterface::GROUP_ID, ['null' => true]);
        } else {
            $this->getCollection()->addFieldToFilter(
                $field,
                [$filter->getConditionType() => $filter->getValue()]
            );
        }
    }

    public function _resetState(): void
    {
        $this->collection = null;
    }
}
