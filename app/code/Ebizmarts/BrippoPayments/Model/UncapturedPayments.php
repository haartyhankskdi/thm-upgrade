<?php

namespace Ebizmarts\BrippoPayments\Model;

use Ebizmarts\BrippoPayments\Api\Data\UncapturedPaymentsInterface;
use Magento\Framework\Model\AbstractModel;

class UncapturedPayments extends AbstractModel implements UncapturedPaymentsInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel\UncapturedPayments::class);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->_getData(UncapturedPaymentsInterface::ID);
    }

    /**
     * @param string $id
     * @return void
     */
    public function setId($id)
    {
        $this->setData(UncapturedPaymentsInterface::ID, $id);
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->_getData(UncapturedPaymentsInterface::STORE_ID);
    }

    /**
     * @param string $storeId
     * @return void
     */
    public function setStoreId($storeId)
    {
        $this->setData(UncapturedPaymentsInterface::STORE_ID, $storeId);
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->_getData(UncapturedPaymentsInterface::COUNT);
    }

    /**
     * @param string $count
     * @return void
     */
    public function setCount($count)
    {
        $this->setData(UncapturedPaymentsInterface::COUNT, $count);
    }

}
