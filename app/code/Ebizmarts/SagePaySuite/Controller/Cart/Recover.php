<?php

namespace Ebizmarts\SagePaySuite\Controller\Cart;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Ebizmarts\SagePaySuite\Model\RecoverCart;
use Magento\Framework\Message\ManagerInterface;
use function urldecode;

class Recover implements ActionInterface
{
    /** @var RecoverCart */
    private $recoverCart;
    /**
     * @var RedirectInterface
     */
    private $redirect;
    /**
     * @var ResponseInterface
     */
    private $response;

    /** @var Http $request */
    private $request;

    /** @var ManagerInterface */
    private $messageManager;

    public function __construct(
        RecoverCart $recoverCart,
        RedirectInterface $redirect,
        ResponseInterface $response,
        Http $request,
        ManagerInterface $messageManager
    ) {
        $this->recoverCart = $recoverCart;
        $this->redirect    = $redirect;
        $this->response    = $response;
        $this->request     = $request;
        $this->messageManager = $messageManager;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->recoverCart->setShouldCancelOrders(false)->execute();
        $this->tryAddErrorMessage();
        $this->redirect->redirect($this->response, 'checkout/cart');
        return $this->response;
    }

    private function tryAddErrorMessage()
    {
        if (!empty($this->request->getHeader('referer'))) {
            $referer = $this->request->getHeader('referer');
            $startPositionMessage = strpos($referer, 'message=') + 8;
            $errorMessageEncoded = substr($referer, $startPositionMessage);
            $errorMessage = urldecode($errorMessageEncoded);
            if (!empty($errorMessage)) {
                $this->messageManager->addErrorMessage($errorMessage);
            }
        }
    }
}
