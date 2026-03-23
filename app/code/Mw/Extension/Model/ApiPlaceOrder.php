<?php



namespace Mw\Extension\Model;
use Zend\Log\Writer\Stream;

use Zend\Log\Logger;



use Mw\Extension\Api\ApiPlaceOrderInterface;



class ApiPlaceOrder implements ApiPlaceOrderInterface

{



    protected $quoteFactory;



    protected $orderSender;



    public function __construct(

        \Magento\Framework\Webapi\Rest\Request $request,

        \Magento\Sales\Api\Data\OrderInterface $order,

        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender

    ) {

        $this->request = $request;

        $this->order = $order;

        $this->orderSender = $orderSender;
    }



    public function placeOrderForCustomer()

    {

        $data = $this->request->getBodyParams();

        $response = [

            'status' => 0,

            'message' => 'Error while adding payment transaction to the order.'

        ];

        if (count($data)) {

            try {

                $paymentInfo = $data['paymentInfo'];

                $orderId = $data['orderId'];

                $order = $this->order->load($orderId);

                $order_payment = $order->getPayment();

                $additionalData = $order_payment->getAdditionalInformation();

                $additionalData_update =   $paymentInfo;

                //  Combine old AdditionalInformation with new Data

                $additionalData_complete = array_merge($additionalData, $additionalData_update);



                // Store updated data in AdditionalInformation

                $order_payment->setAdditionalInformation($additionalData_complete);

                $order_payment->save();

                $order_payment_display = $order->getPayment()->getData();

                $response = [

                    'status' => 1,

                    'message' => 'success',

                    'order_payment_display' => $order_payment_display

                ];
            } catch (\Exception $e) {
                $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/app/placeOrderForCustomer.log');

                $logger = new \Zend\Log\Logger();

                $logger->addWriter($writer);

                $logger->info('---------------------------------------------------------------------');

                $logger->info($e->getMessage());

                $response = ['status' => 0, 'message' => $e->getMessage()];
            }
        }

        return json_encode($response);

        exit();
    }



    public function orderMail()

    {

        $data = $this->request->getBodyParams();

        $response = [

            'status' => 0,

            'message' => 'Error while adding payment transaction to the order.'

        ];

        if (count($data)) {
            try {
                $orderId = $data['orderId'];
                $order = $this->order->load($orderId);

                // $order->setCanSendNewEmailFlag(true);
                // $order->save();
                if (isset($data['comment']) && !empty($data['comment'])) {
                    $order->setData('osc_order_comment', $data['comment']);
                    $order->save();
                }

                $this->orderSender->send($order);

                $response = [

                    'status' => 1,

                    'message' => 'success',

                    // 'body' => $order->get()

                ];
            } catch (\Exception $e) {

                $response = ['status' => 0, 'message' => $e->getMessage()];
            }
        }

        return json_encode($response);

        exit();
    }



    public function getOrderId()

    {

        $data = $this->request->getBodyParams();

        $response = [

            'status' => 0,

            'message' => 'Error while getting Order Information.'

        ];

        if (count($data)) {

            try {

                $orderId = $data['orderId'];

                $order = $this->order->load($orderId);

                $response = [

                    'status' => 1,

                    'message' => 'success',

                    'data' => $order->getIncrementId()

                ];
            } catch (\Exception $e) {

                $response = ['status' => 0, 'message' => $e->getMessage()];
            }
        }

        return json_encode($response);

        exit();
    }
}
