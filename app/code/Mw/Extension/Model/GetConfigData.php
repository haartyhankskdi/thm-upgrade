<?php

namespace Mw\Extension\Model;

use Mw\Extension\Api\GetConfigInterface;
use Magento\Framework\Webapi\Rest\Request;

class GetConfigData implements GetConfigInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    private $scopeConfig;

    /**
     * @var Request
     */
    protected $request;
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Webapi\Rest\Request $request
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
    }
    public function getFreeShippingRate()
    {
        try {
            $amount = $this->scopeConfig->getValue('carriers/freeshipping/free_shipping_subtotal', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $response = [
                'status' => 1,
                'message' => 'success',
                "data" => $amount
            ];
        } catch (\Exception $e) {
            $response = ['status' => 0, 'message' => $e->getMessage()];
        }
        return json_encode($response);
        exit();
    }

    public function getBraintreeNounce()
    {
        require_once __DIR__ . '/Braintree/lib/autoload.php';
        try {
            $data = $this->request->getBodyParams();
            $paymentInfo = $data['paymentInfo'];
            $isTesting = $data['isTesting'];
            if (!$isTesting) {
                $gateway = new \Braintree\Gateway([
                    'environment' => 'production',
                    'merchantId' => 'mtwfdbccfcnjbcc6',
                    'publicKey' => 'dkyp4fv7m9ck5dm9',
                    'privateKey' => 'f96d86dd17c19d1b0cdb5da16ed31893'
                ]);
            } else {
                $gateway = new \Braintree\Gateway([
                    'environment' => 'sandbox',
                    'merchantId' => '86gh95x9hxckmdq6',
                    'publicKey' => 'mvsq893cv7bqg8fw',
                    'privateKey' => 'f1de25d8ecbebe4a05d2d8794e9b4ad6'
                ]);
            }
            $result = $gateway->transaction()->sale([
                'amount' => $paymentInfo['amount'],
                'paymentMethodNonce' => $paymentInfo['nonce'],
                'options' => ['submitForSettlement' => true]
            ]);
            if ($result->success) {
                $payment_result['id'] = $result->transaction->id;
                $payment_result['globalId'] = $result->transaction->globalId;
                $payment_result['paymentInstrumentType'] = $result->transaction->paymentInstrumentType;
                $payment_result['status'] = $result->transaction->status;
                $payment_result['type'] = $result->transaction->type;
                $payment_result['processorResponseType'] = $result->transaction->processorResponseType;
                echo json_encode(array(
                    'status' => 1,
                    'message' => "success",
                    'result' => $result,
                    'data' => $payment_result
                ));
                exit();
            } elseif ($result->transaction) {
                // print_r("\n  code: " . $result->transaction->processorResponseCode);
                // print_r("\n  text: " . $result->transaction->processorResponseText);
                echo json_encode(array(
                    'status' => 0,
                    'message' => "Error processing transaction, " . $result->transaction->processorResponseText . ":" . $result->transaction->processorResponseCode,
                    'data' => $result->transaction->processorResponseCode . ":" . $result->transaction->processorResponseText
                ));
                exit();
            } else {
                // print_r($result->errors->deepAll());
                echo json_encode(array(
                    'status' => 0,
                    'message' => "Card Validation errors " . $result->message,
                    'data' => $result->errors->deepAll(),
                    'result' => $paymentInfo
                ));
                exit();
            }
        } catch (\Exception $e) {
            $response = ['status' => 0, 'message' => $e->getMessage()];
        }
        return json_encode($response);
        exit();
    }
}
