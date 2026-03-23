<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Model\ResourceModel\Invitation;

use Exception;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Mageplaza\RewardPointsPro\Model\ResourceModel\AbstractCollection;
use Mageplaza\RewardPointsUltimate\Api\Data\InvitationSearchResultInterface;
use Mageplaza\RewardPointsUltimate\Model\Invitation;

/**
 * Class Collection
 * @package Mageplaza\RewardPointsUltimate\Model\ResourceModel\Invitation
 */
class Collection extends AbstractCollection implements InvitationSearchResultInterface
{
    /**
     * @type string
     */
    protected $_idFieldName = 'invitation_id';

    /**
     * @var SearchCriteriaInterface
     */
    protected $searchCriteria;

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(
            Invitation::class,
            \Mageplaza\RewardPointsUltimate\Model\ResourceModel\Invitation::class
        );
    }

    /**
     * @param ExtensibleDataInterface[] $items
     *
     * @return $this
     * @throws Exception
     */
    public function setItems(?array $items = null)
    {
        if (!$items) {
            return $this;
        }
        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    /**
     * Get search criteria.
     *
     * @return SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
    }

    /**
     * Set search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(?SearchCriteriaInterface $searchCriteria = null)
    {
        $this->searchCriteria = $searchCriteria;

        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }
}
