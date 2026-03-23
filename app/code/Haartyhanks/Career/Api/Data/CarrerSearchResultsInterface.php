<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Haartyhanks\Career\Api\Data;

interface CarrerSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Carrer list.
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface[]
     */
    public function getItems();

    /**
     * Set first_name list.
     * @param \Haartyhanks\Career\Api\Data\CarrerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

