<?php

namespace Ebizmarts\BrippoPayments\Model\Config\Source;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Option\ArrayInterface;

class AvailableCustomerGroups implements ArrayInterface
{
    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @param GroupManagementInterface $groupManagement Group management interface
     */
    public function __construct(GroupManagementInterface $groupManagement)
    {
        $this->groupManagement = $groupManagement;
    }

    /**
     * @return array Options array
     */
    public function toOptionArray()
    {
        $groups = array_merge(
            [$this->groupManagement->getNotLoggedInGroup()],
            $this->groupManagement->getLoggedInGroups()
        );
        $options = [];
        foreach ($groups as $group) {
            $options[] = ['value' => $group->getId(), 'label' => $group->getCode()];
        }
        return $options;
    }
}
