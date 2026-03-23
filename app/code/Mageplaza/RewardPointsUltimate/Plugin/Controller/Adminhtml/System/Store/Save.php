<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
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

namespace Mageplaza\RewardPointsUltimate\Plugin\Controller\Adminhtml\System\Store;

use Exception;
use Magento\Backend\Controller\Adminhtml\System\Store\Save as StoreSave;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\RequestInterface;
use Mageplaza\RewardPointsUltimate\Helper\Data;

/**
 * Class Save
 * @package Mageplaza\RewardPointsUltimate\Plugin\Controller\Adminhtml\System\Store
 */
class Save
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Save constructor.
     *
     * @param RequestInterface $request
     * @param Data $helperData
     */
    public function __construct(
        RequestInterface $request,
        Data $helperData
    ) {
        $this->request    = $request;
        $this->helperData = $helperData;
    }

    /**
     * @param StoreSave $subject
     * @param Redirect $result
     *
     * @return mixed
     * @throws Exception
     */
    public function afterExecute(StoreSave $subject, $result)
    {
        $postData = $this->request->getPostValue();
        if ($postData['store_type'] === 'website') {
            $this->helperData->updateCustomerGroupAndWebsiteForBaseMileStone();
        }

        return $result;
    }
}
