<?php
declare(strict_types=1);

namespace Ebizmarts\BrippoPayments\Api\Data;

interface UncapturedPaymentsInterface
{
    const ID = 'id';
    const STORE_ID = 'store_id';
    const COUNT = 'count';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return void
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     * @return void
     */
    public function setStoreid($storeId);

    /**
     * @return int
     */
    public function getCount();

    /**
     * @param int $count
     * @return void
     */
    public function setCount($count);
}
