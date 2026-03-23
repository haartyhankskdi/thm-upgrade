<?php

namespace Ebizmarts\SagePaySuite\Controller\Pi;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Store\Model\StoreManagerInterface;

use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\Multishipping\Pi\PiCallbackThreeDManagement;

class Callback3Dv2Multishipping implements ActionInterface, CsrfAwareActionInterface
{
    private const ERROR_MESSAGE = 'Something went wrong, order not available.';

    /** @var PiCallbackThreeDManagement */
    private $callbackManager;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var ResponseInterface
     */
    private $response;
    /**
     * @var UrlInterface
     */
    private $url;

    /**
    * @var StoreManagerInterface
    */
    private $storeManager;

    /**
     * @param PiCallbackThreeDManagement $callbackManager
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param UrlInterface $url
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        PiCallbackThreeDManagement $callbackManager,
        RequestInterface $request,
        ResponseInterface $response,
        UrlInterface $url,
        StoreManagerInterface $storeManager
    ) {
        $this->callbackManager             = $callbackManager;
        $this->request                     = $request;
        $this->response                    = $response;
        $this->url                         = $url;
        $this->storeManager                = $storeManager;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $cres = $this->getRequest()->getPost('cres');

        $result = $this->callbackManager->handleCallbackData($cres);

        if ($result === null) {
            $this->_javascriptRedirect('multishipping/checkout/success', null, null, __($this->getErrorMessage()));
        } elseif ($result[1] != Config::SUCCESS_STATUS) {
            $this->_javascriptRedirect('multishipping/checkout/success', null, null, $result[1]);
        } else {
            $this->_javascriptRedirect($result[0], $result[1], $result[2], null);
        }
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
     *  Perform custom request validation.
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

    /**
     * @param $url
     * @param $status
     * @param $orderIds
     * @param $errorMessage
     */
    private function _javascriptRedirect($url, $status = null, $orderIds = null, $errorMessage = null)
    {
        $params = [
            '_nosid' => true,
            '_secure' => true,
            '_store' => $this->storeManager->getStore()->getId()
        ];
        $finalUrl = $this->url->getUrl($url, $params);

        if ($status === null) {
            $finalUrl .= "?status=error";
        } else {
            $finalUrl .= "?status=$status";
        }

        if ($orderIds !== null) {
            $count = 0;
            foreach ($orderIds as $orderId) {
                $finalUrl .= "&orderId" . $count . "=" . $orderId;
                $count ++;
            }
        }

        if ($errorMessage !== null) {
            $errorMessage = urlencode($errorMessage);
            if ($orderIds !== null) {
                $finalUrl .= "&errorMessage=$errorMessage";
            } else {
                $finalUrl .= "?errorMessage=$errorMessage";
            }
        }

        //redirect to success via javascript
        $this
            ->response
            ->setBody(
                '<script>window.top.location.href = "'
                . $finalUrl
                . '";</script>'
            );
    }

    /**
     * @return string
     */
    private function getErrorMessage()
    {
        return self::ERROR_MESSAGE;
    }

    /**
     * @return RequestInterface
     */
    protected function getRequest()
    {
        return $this->request;
    }
}
