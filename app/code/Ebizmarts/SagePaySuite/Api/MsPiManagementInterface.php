<?php

namespace Ebizmarts\SagePaySuite\Api;

use Ebizmarts\SagePaySuite\Api\Data\PiRequest;

/**
 *
 * @api
 */
interface MsPiManagementInterface
{
    /**
     * @param int $cartId
     * @param PiRequest $requestData
     * @return \Ebizmarts\SagePaySuite\Api\Data\PiResultInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function handleTransactionData($cartId, PiRequest $requestData);

    /**
     * This function saves the card token
     *
     * @param int $cartId
     * @param PiRequest $requestData
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return \Ebizmarts\SagePaySuite\Api\Data\PiResultInterface
     */
    public function saveCardToken($cartId, PiRequest $requestData);

    /**
     * @param int $cartId
     * @param PiRequest $requestData
     * @return mixed|void
     * @throws \Exception
     */
    public function saveScaParams($cartId, PiRequest $requestData);
}
