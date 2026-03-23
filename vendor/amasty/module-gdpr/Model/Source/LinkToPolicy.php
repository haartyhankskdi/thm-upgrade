<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Source;

use Magento\Cms\Model\Page;
use Magento\Cms\Model\ResourceModel\Page\Collection;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class LinkToPolicy implements OptionSourceInterface
{
    public const PRIVACY_POLICY = '#';

    /**
     * @var array
     */
    private $renderedOptions = [];

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
     * @inheritDoc
     */
    public function toOptionArray()
    {
        if (!$this->renderedOptions) {
            /** @var Collection $collection**/
            $collection = $this->collectionFactory->create();

            /** @var Page $page**/
            foreach ($collection as $page) {
                $this->renderedOptions[] = [
                    'label' => $page->getTitle(),
                    'value' => $page->getId()
                ];
            }

        }

        return $this->renderedOptions;
    }
}
