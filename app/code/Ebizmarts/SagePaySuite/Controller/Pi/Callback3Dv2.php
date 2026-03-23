<?php

namespace Ebizmarts\SagePaySuite\Controller\Pi;

use Ebizmarts\SagePaySuite\Api\Data\PiRequestManagerFactory;
use Ebizmarts\SagePaySuite\Api\Data\PiRequestManager;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\CryptAndCodeData;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\ThreeDSecureCallbackManagement;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\QuoteRepository;

class Callback3Dv2 implements ActionInterface, CsrfAwareActionInterface
{
    /** @var Config */
    private $config;

    /** @var ThreeDSecureCallbackManagement */
    private $requester;

    /** @var PiRequestManager */
    private $piRequestManagerDataFactory;

    /** @var QuoteRepository */
    private $quoteRepository;

    /** @var CryptAndCodeData */
    private $cryptAndCode;

    /** @var OrderLoader */
    private $orderLoader;

    /** @var Logger */
    private $suiteLogger;

    /** @var RequestInterface */
    private $request;

    /** @var UrlInterface */
    private $url;

    /** @var ResponseInterface  */
    private $response;

    /**
     * @param Config $config
     * @param ThreeDSecureCallbackManagement $requester
     * @param PiRequestManagerFactory $piReqManagerFactory
     * @param QuoteRepository $quoteRepository
     * @param CryptAndCodeData $cryptAndCode
     * @param OrderLoader $orderLoader
     * @param Logger $suiteLogger
     * @param RequestInterface $request
     * @param UrlInterface $url
     * @param ResponseInterface $response
     **/
    public function __construct(
        Config $config,
        ThreeDSecureCallbackManagement $requester,
        PiRequestManagerFactory $piReqManagerFactory,
        QuoteRepository $quoteRepository,
        CryptAndCodeData $cryptAndCode,
        OrderLoader $orderLoader,
        Logger $suiteLogger,
        RequestInterface $request,
        UrlInterface $url,
        ResponseInterface $response
    ) {
        $this->config = $config;
        $this->config->setMethodCode(Config::METHOD_PI);
        $this->quoteRepository             = $quoteRepository;
        $this->requester                   = $requester;
        $this->piRequestManagerDataFactory = $piReqManagerFactory;
        $this->cryptAndCode                = $cryptAndCode;
        $this->orderLoader                 = $orderLoader;
        $this->suiteLogger                 = $suiteLogger;
        $this->request                     = $request;
        $this->url                         = $url;
        $this->response                    = $response;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $orderId = $orderIdEncrypted = null;
        $quoteIdEncrypted = $this->getRequest()->getParam("quoteId");
        $quoteIdFromParams = $this->cryptAndCode->decodeAndDecrypt($quoteIdEncrypted);
        try {
            $quote = $this->quoteRepository->get((int)$quoteIdFromParams);
            $order = $this->orderLoader->loadOrderFromQuote($quote);
            $orderId = (int)$order->getId();
            $orderIdEncrypted = $this->cryptAndCode->encryptAndEncode((string)$orderId);
            $customerId = $order->getCustomerId();
            $this->suiteLogger->debugLog(
                "OrderId: " . $orderId . " QuoteId: " . $quoteIdFromParams . " CustomerId: " . $customerId,
                [__LINE__, __METHOD__]
            );
            $this->suiteLogger->debugLog($order->getData(), [__LINE__, __METHOD__]);

            $payment = $order->getPayment();
            $this->suiteLogger->debugLog($payment->getData(), [__LINE__, __METHOD__]);

            /** @var PiRequestManager $data */
            $data = $this->piRequestManagerDataFactory->create();
            $data->setTransactionId($payment->getLastTransId());
            $data->setCres($this->getRequest()->getPost('cres'));
            $data->setVendorName($this->config->getVendorname());
            $data->setMode($this->config->getMode());
            $data->setPaymentAction($this->config->getSagepayPaymentAction());
            $data->setSaveToken((bool)$this->getRequest()->getParam("saveToken"));

            $this->requester->setRequestData($data);

            $this->setRequestParamsForConfirmPayment($orderId, $order);

            $response = $this->requester->placeOrder();

            $this->suiteLogger->orderEndLog($order->getIncrementId(), $quoteIdFromParams, $payment->getLastTransId());
            if ($response->getErrorMessage() === null) {
                $this->javascriptRedirect('elavon/pi/success', $quoteIdEncrypted, $orderIdEncrypted);
            } else {
                $this->suiteLogger->sageLog(
                    Logger::LOG_EXCEPTION,
                    $response->getErrorMessage(),
                    [__METHOD__, __LINE__]
                );
                $this->javascriptRedirect(
                    'elavon/pi/failure',
                    $quoteIdEncrypted,
                    $orderIdEncrypted,
                    __("Unfortunately the order payment failed, please try again, "
                    ."with another payment method or ask the store for help.")
                );
            }
        } catch (ApiException $apiException) {
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $apiException->getMessage() .
                " - orderId: " . $orderId, [__METHOD__, __LINE__]);
            $this->suiteLogger->sageLog(
                Logger::LOG_EXCEPTION,
                $apiException->getTraceAsString(),
                [__METHOD__, __LINE__]
            );
            $this->javascriptRedirect(
                'elavon/pi/failure',
                $quoteIdEncrypted,
                $orderIdEncrypted,
                $apiException->getUserMessage()
            );
        } catch (\Exception $e) {
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $e->getMessage() .
                " - orderId: " . $orderId, [__METHOD__, __LINE__]);
            $this->suiteLogger->sageLog(
                Logger::LOG_EXCEPTION,
                $e->getTraceAsString(),
                [__METHOD__, __LINE__]
            );
            $this->javascriptRedirect(
                'elavon/pi/failure',
                $quoteIdEncrypted,
                $orderIdEncrypted,
                $e->getMessage()
            );
        }
        return $this->response;
    }

    /**
     * @param $url
     * @param $quoteId
     * @param $orderId
     * @param $errorMessage
     */
    private function javascriptRedirect($url, $quoteId = null, $orderId = null, $errorMessage = null)
    {
        $params = [
            '_nosid' => true,
            '_secure' => true,
            '_store' => $this->config->getCurrentStoreId()
        ];
        $finalUrl = $this->url->getUrl($url, $params);
        if ($quoteId !== null) {
            $finalUrl .= "?quoteId=$quoteId";
        }

        if ($orderId !== null) {
            if ($quoteId !== null) {
                $finalUrl .= "&orderId=$orderId";
            } else {
                $finalUrl .= "?orderId=$orderId";
            }
        }

        if ($errorMessage !== null) {
            $errorMessage = urlencode($errorMessage);
            if ($quoteId !== null || $orderId !== null) {
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
     * @param int $orderId
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    private function setRequestParamsForConfirmPayment(int $orderId, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        $orderId = $this->encryptAndEncode((string)$orderId);
        $quoteId = $this->encryptAndEncode((string)$order->getQuoteId());

        $this->getRequest()->setParams([
            'orderId' => $orderId,
            'quoteId' => $quoteId
        ]);
    }

    /**
     * @param $data
     * @return string
     */
    public function encryptAndEncode($data)
    {
        return $this->cryptAndCode->encryptAndEncode($data);
    }

    /**
     * @return bool
     */
    public function getSaveToken()
    {
        if ($this->getRequest()->getParam("st") === 'true') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return RequestInterface
     */
    protected function getRequest()
    {
        return $this->request;
    }
}
