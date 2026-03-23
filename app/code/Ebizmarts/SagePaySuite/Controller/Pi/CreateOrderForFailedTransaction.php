<?php

namespace Ebizmarts\SagePaySuite\Controller\Pi;

use Ebizmarts\SagePaySuite\Helper\Checkout;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Ebizmarts\SagePaySuite\Model\Api\Reporting;
use Ebizmarts\SagePaySuite\Model\CryptAndCodeData;
use Ebizmarts\SagePaySuite\Model\SessionInterface as SagePaySession;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class CreateOrderForFailedTransaction implements ActionInterface, CsrfAwareActionInterface
{
    public const PATH_TO_REDIRECT_FAILURE = 'elavon/pi/failure';

    /** @var Reporting */
    private $reportingApi;

    /** @var CartRepositoryInterface */
    private $quoteRepository;

    /** @var ManagerInterface */
    private $eventManager;

    /** @var Logger */
    private $suiteLogger;

    /** @var Session */
    private $checkoutSession;

    /** @var CryptAndCodeData */
    private $cryptAndCode;

    /** @var Checkout */
    private $sagepayCheckout;

    /** @var RequestInterface */
    private $request;

    /** @var ResponseInterface */
    private $response;

    /** @var UrlInterface */
    private $url;

    /**
     * @var StoreManagerInterface */
    private $storeManager;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param Reporting $reportingApi
     * @param ManagerInterface $eventManager
     * @param Logger $suiteLogger
     * @param Session $checkoutSession
     * @param CryptAndCodeData $cryptAndCode
     * @param Checkout $sagepayCheckout
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param UrlInterface $url
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Reporting $reportingApi,
        ManagerInterface $eventManager,
        Logger $suiteLogger,
        Session $checkoutSession,
        CryptAndCodeData $cryptAndCode,
        Checkout $sagepayCheckout,
        RequestInterface $request,
        ResponseInterface $response,
        UrlInterface $url,
        StoreManagerInterface $storeManager
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->reportingApi = $reportingApi;
        $this->eventManager = $eventManager;
        $this->suiteLogger = $suiteLogger;
        $this->checkoutSession = $checkoutSession;
        $this->cryptAndCode = $cryptAndCode;
        $this->sagepayCheckout = $sagepayCheckout;
        $this->request       = $request;
        $this->response      = $response;
        $this->url           = $url;
        $this->storeManager    = $storeManager;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $orderId = null;
        $orderIdEncrypted = null;
        $quoteIdEncrypted = $this->getRequest()->getParam("quoteId");
        $errorMessage = $this->getRequest()->getParam("errorMessage");
        $quoteIdFromParams = $this->cryptAndCode->decodeAndDecrypt($quoteIdEncrypted);
        try {
            $quote = $this->quoteRepository->get((int)$quoteIdFromParams);
            $transactionId = $this->getRequest()->getParam('transactionId');
            $transactionDetails = $this->reportingApi->getTransactionDetailsByVpstxid(
                $transactionId,
                $quote->getStoreId()
            );
            if ($transactionDetails !== null) {
                $order = $this->sagepayCheckout->placeOrder($quote);
                if ($order !== null) {
                    $orderId = (int)$order->getId();//set pre-saved order flag in checkout session
                    $orderIdEncrypted = $this->cryptAndCode->encryptAndEncode((string)$orderId);
                    $this->checkoutSession->setData(SagePaySession::PRESAVED_PENDING_ORDER_KEY, $orderId);
                    $this->checkoutSession->setData(SagePaySession::CONVERTING_QUOTE_TO_ORDER, 1);
                    $customerId = $order->getCustomerId();
                    $this->suiteLogger->debugLog(
                        "OrderId: " . $orderId . " QuoteId: " . $quoteIdFromParams . " CustomerId: " . $customerId,
                        [__LINE__, __METHOD__]
                    );
                    $this->suiteLogger->debugLog($order->getData(), [__LINE__, __METHOD__]);

                    $payment = $order->getPayment();
                    $this->suiteLogger->debugLog($payment->getData(), [__LINE__, __METHOD__]);

                    $payment->setTransactionId($transactionId);
                    $payment->setLastTransId($transactionId);
                    $payment->setAdditionalInformation('vendorTxCode', $transactionDetails->vendortxcode);
                    if (!empty($transactionDetails->status)) {
                        $payment->setAdditionalInformation('statusDetail', $transactionDetails->status);
                    } elseif (!empty($transactionDetails->invalidstatusdetail)) {
                        $payment->setAdditionalInformation('statusDetail', $transactionDetails->invalidstatusdetail);
                    }
                    $payment->save();
                    $this->eventManager->dispatch('checkout_submit_all_after', ['order' => $order, 'quote' => $quote]);
                    $order->save();
                    $this->checkoutSession->setData(SagePaySession::CONVERTING_QUOTE_TO_ORDER, 0);

                    $this->suiteLogger->orderEndLog(
                        $order->getIncrementId(),
                        $quoteIdFromParams,
                        $payment->getLastTransId()
                    );
                    $this->javascriptRedirect(
                        $quoteIdFromParams,
                        $orderIdEncrypted,
                        $errorMessage
                    );
                }
            }
        } catch (ApiException $apiException) {
            $errorException = $errorMessage . "\r\n";
            $errorException .= $apiException->getUserMessage();
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $errorException .
            " - orderId: " . $orderId, [__METHOD__, __LINE__]);
            $this->suiteLogger->sageLog(
                Logger::LOG_EXCEPTION,
                $apiException->getTraceAsString(),
                [__METHOD__, __LINE__]
            );
            $this->javascriptRedirect($quoteIdEncrypted, $orderIdEncrypted, $apiException->getUserMessage());
        } catch (\Exception $e) {
            $errorException = $errorMessage . "\r\n";
            $errorException .= $e->getMessage();
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $errorException .
            " - orderId: " . $orderId, [__METHOD__, __LINE__]);
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $e->getTraceAsString(), [__METHOD__, __LINE__]);
            $this->javascriptRedirect($quoteIdEncrypted, $orderIdEncrypted, $e->getMessage());
        }
        return $this->response;
    }

    /**
     * @param $quoteId
     * @param $orderId
     * @param $errorMessage
     */
    private function javascriptRedirect($quoteId = null, $orderId = null, $errorMessage = null)
    {
        $params = [
            '_nosid' => true,
            '_secure' => true,
            '_store' => $this->storeManager->getStore()->getId()
        ];
        $finalUrl = $this->url->getUrl(self::PATH_TO_REDIRECT_FAILURE, $params);
        if (!empty($quoteId)) {
            $finalUrl .= "?quoteId=$quoteId";
        }

        if (!empty($orderId)) {
            if (!empty($quoteId)) {
                $finalUrl .= "&orderId=$orderId";
            } else {
                $finalUrl .= "?orderId=$orderId";
            }
        }

        if (!empty($errorMessage)) {
            $errorMessage = urlencode($errorMessage);
            if (!empty($quoteId) || !empty($orderId)) {
                $finalUrl .= "&errorMessage=$errorMessage";
            } else {
                $finalUrl .= "?errorMessage=$errorMessage";
            }
        }

        //redirect to failure via javascript
        $this
            ->response
            ->setBody(
                '<script>window.top.location.href = "'
                . $finalUrl
                . '";</script>'
            );
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

    /**
     * @return RequestInterface
     */
    protected function getRequest()
    {
        return $this->request;
    }
}
