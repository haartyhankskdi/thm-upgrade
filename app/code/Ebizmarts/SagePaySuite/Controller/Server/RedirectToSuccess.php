<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Server;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Store\Model\StoreManagerInterface;

class RedirectToSuccess implements ActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var UrlInterface
     */
    private $url;
    /**
     * @var ResponseInterface
     */
    private $response;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param RequestInterface $request
     * @param UrlInterface $url
     * @param ResponseInterface $response
     * @param StoreManagerInterface $storemanager
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $url,
        ResponseInterface $response,
        StoreManagerInterface $storeManager
    ) {
        $this->request         = $request;
        $this->url             = $url;
        $this->response        = $response;
        $this->storeManager    = $storeManager;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $request = $this->getRequest();
        $storeId = $this->storeManager->getStore()->getId();
        $encryptedQuoteId = $request->getParam("quoteid");
        $url = $this->url->getUrl('*/*/success', ['_nosid' => true, '_secure' => true, '_store' => $storeId]);
        if (strpos($url, 'quoteid') === false) {
            $url .= '?quoteid=' . urlencode($encryptedQuoteId);
        }
        //redirect to success via javascript
        $this->getResponse()->setBody('<script>window.top.location.href = "' . $url . '";</script>');
        return $this->response;
    }
    protected function getRequest()
    {
        return $this->request;
    }
    protected function getResponse()
    {
        return $this->response;
    }
}
