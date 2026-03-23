<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Haartyhanks\Career\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface CarrerRepositoryInterface
{

    /**
     * Save Carrer
     * @param \Haartyhanks\Career\Api\Data\CarrerInterface $carrer
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Haartyhanks\Career\Api\Data\CarrerInterface $carrer
    );

    /**
     * Retrieve Carrer
     * @param string $carrerId
     * @return \Haartyhanks\Career\Api\Data\CarrerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($carrerId);

    /**
     * Retrieve Carrer matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Haartyhanks\Career\Api\Data\CarrerSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Carrer
     * @param \Haartyhanks\Career\Api\Data\CarrerInterface $carrer
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Haartyhanks\Career\Api\Data\CarrerInterface $carrer
    );

    /**
     * Delete Carrer by ID
     * @param string $carrerId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($carrerId);
}

