<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Paypal;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\Layout;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Processing implements ActionInterface, CsrfAwareActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var ResponseInterface
     */
    private $response;
    /**
     * @var Layout
     */
    private $layout;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param Layout $layout
     */
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        Layout $layout
    ) {
        $this->request  = $request;
        $this->response = $response;
        $this->layout   = $layout;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $body = $this->layout->getLayout()->createBlock(
            \Ebizmarts\SagePaySuite\Block\Paypal\Processing::class
        )
        ->setData(
            ["paypal_post"=>$this->getRequest()->getPost()]
        )->toHtml();

        $this->response->setBody($body);
        return $this->response;
    }

    /**
     * Create exception in case CSRF validation failed.
     * Return null if default exception will suffice.
     *
     * @param RequestInterface $request
     *
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Perform custom request validation.
     * Return null if default validation is needed.
     *
     * @param RequestInterface $request
     *
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
    protected function getRequest()
    {
        return $this->request;
    }
}
