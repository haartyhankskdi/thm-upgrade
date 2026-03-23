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
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Model\Source\Customer;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Groups
 * @package Mageplaza\RewardPointsPro\Model\Source\Customer
 */
class Groups implements ArrayInterface
{
    /**
     * @var $_options
     */
    protected $_options;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Groups constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        RequestInterface $request
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->request           = $request;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = $this->collectionFactory->create()->load()->toOptionHash();
        }
        if ($this->request->getFullActionName() === 'mprewardultimate_referral_index' && isset($this->_options[0]) && $this->_options[0] === 'NOT LOGGED IN') {
            unset($this->_options[0]);
        }

        return $this->_options;
    }
}
