<?php

namespace Ebizmarts\BrippoPayments\Controller\Api;

use DateTime;
use DateTimeZone;
use Ebizmarts\BrippoPayments\Helper\Data;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\PlatformService\PlatformService;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;
use Ebizmarts\BrippoPayments\Plugin\CsrfFilter;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderTimeline extends Action
{
    /**
     * @var Logger
     */
    protected $logger;
    protected $dataHelper;
    protected $orderRepository;
    protected $searchCriteriaBuilder;
    protected $resultJsonFactory;

    /**
     * @param Context $context
     * @param Logger $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param JsonFactory $resultJsonFactory
     * @param CsrfFilter $csrfFilter
     * @param DataHelper $dataHelper
     */
    public function __construct(
        Context $context,
        Logger $logger,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        JsonFactory $resultJsonFactory,
        CsrfFilter $csrfFilter,
        Data $dataHelper
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resultJsonFactory = $resultJsonFactory;
        $csrfFilter->filterCrsfInterfaceImplementation($this);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $statusCode = 200;

        try {
            $orderIncrementId = $this->getRequest()->getParam(PlatformService::PARAM_ORDER_INCREMENT_ID);
            if (empty($orderIncrementId)) {
                $statusCode = 400;
                throw new LocalizedException(__('Invalid order increment ID'));
            }

            $paymentIntentId = $this->getRequest()->getParam(PlatformService::PARAM_PAYMENT_INTENT_ID);
//            if (empty($paymentIntentId)) {
//                $statusCode = 400;
//                throw new LocalizedException(__('Invalid payment intent ID'));
//            }

            $order = $this->getOrderByIncrementId($orderIncrementId);
            if (empty($order) || empty($order->getEntityId())) {
                $statusCode = 404;
                throw new LocalizedException(__('Order not found'));
            }

            if (!$this->dataHelper->wasOrderPaidWithBrippo($order)) {
                $statusCode = 405;
                throw new LocalizedException(__('Not a Brippo order'));
            }

            $orderPaymentIntentId = $order->getPayment()->getAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_PAYMENT_INTENT_ID
            );
            if (!empty($orderPaymentIntentId) && $orderPaymentIntentId !== $paymentIntentId) {
                $statusCode = 409;
                throw new LocalizedException(__('Invalid payment intent ID'));
            }

            $orderCreatedAt = new DateTime($order->getCreatedAt(), new DateTimeZone('UTC'));

            $result->setData([
                'status' => $statusCode,
                'timeline' => BrippoOrder::getTimeline($order),
                'created' => $orderCreatedAt->getTimestamp(),
                'current' => [
                    'status' => $order->getStatus(),
                    'state' => $order->getState()
                ]
            ]);
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage(), Logger::INTERNAL_API_LOG);
            $result->setData([
                'status' => $statusCode,
                'message' => $ex->getMessage()
            ]);
        }

        return $result;
    }

    /**
     * @param $incrementId
     * @return OrderInterface|null
     */
    public function getOrderByIncrementId($incrementId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId, 'eq')
            ->create();
        $orderList = $this->orderRepository->getList($searchCriteria)->getItems();

        if (!empty($orderList) && count($orderList) > 0) {
            return reset($orderList);
        } else {
            return null;
        }
    }

    protected function _isAllowed(): bool
    {
        return true;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
