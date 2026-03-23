<?php

namespace Ebizmarts\SagePaySuite\Block\Multishipping;

use Magento\Framework\View\Element\Template\Context;

class ThreeDSecure extends \Magento\Framework\View\Element\Template
{
    /** @var string */
    private $md = null;

    /** @var string */
    private $ascUrl = null;

    /** @var array */
    private $orderIds;

    /** @var string */
    private $creq;

    /**
     * ThreeDSecure constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
        $this->addData(["cache_lifetime" => null]);
    }

    /**
     * @return string
     */
    public function getMd()
    {
        return $this->md;
    }

    /**
     * @return string
     */
    public function getAscUrl()
    {
        return $this->ascUrl;
    }

    /**
     * @return array
     */
    public function getOrderIds()
    {
        return $this->orderIds;
    }

    /**
     * @return string
     */
    public function getCreq()
    {
        return $this->creq;
    }

    /**
     * @param string $md
     */
    public function setMd($md)
    {
        $this->md = $md;
    }

    /**
     * @param string $ascUrl
     */
    public function setAscUrl($ascUrl)
    {
        $this->ascUrl = $ascUrl;
    }

    /**
     * @param array $orderIds
     */
    public function setOrderIds($orderIds)
    {
        $this->orderIds = $orderIds;
    }

    /**
     * @param string $creq
     */
    public function setCreq($creq)
    {
        $this->creq = $creq;
    }
}
