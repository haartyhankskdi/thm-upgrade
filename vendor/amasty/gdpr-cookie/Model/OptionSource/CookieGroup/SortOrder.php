<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\OptionSource\CookieGroup;

use Amasty\GdprCookie\Api\Data\CookieGroupsInterface;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroup\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\DB\Select;

class SortOrder implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $collection = $this->collectionFactory->create();
        $collection->getSelect()
            ->reset(Select::COLUMNS)
            ->columns([CookieGroupsInterface::SORT_ORDER])
            ->distinct(true);
        $collection->setOrder(CookieGroupsInterface::SORT_ORDER, $collection::SORT_ORDER_ASC);

        $optionArray = [];
        /** @var CookieGroupsInterface $cookieGroup */
        foreach ($collection->getItems() as $cookieGroup) {
            $optionArray[] = [
                'value' => $cookieGroup->getSortOrder(),
                'label' => (string)$cookieGroup->getSortOrder()
            ];
        }

        return $optionArray;
    }
}
