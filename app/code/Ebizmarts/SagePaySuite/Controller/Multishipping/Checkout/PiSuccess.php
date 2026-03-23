<?php

namespace Ebizmarts\SagePaySuite\Controller\Multishipping\Checkout;

use Ebizmarts\SagePaySuite\Block\Multishipping\ThreeDSecure;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\MsPayment;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\EcommerceManagement;
use Ebizmarts\SagePaySuite\Model\RecoverCart;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\Generic;
use Magento\Framework\View\Result\PageFactory;
use Magento\Multishipping\Controller\Checkout\Success;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Customer\Model\Url as CustomerUrl;

class PiSuccess extends Success
{
    /** @var MsPayment */
    private $msPayment;

    /** @var Multishipping */
    private $multishipping;

    /** @var State */
    private $state;

    /** @var PageFactory */
    private $resultPageFactory;

    /** @var Session */
    private $checkoutSession;

    /** @var ThreeDSecure */
    private $block;

    /** @var Generic */
    private $session;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var CustomerSession */
    private $customerSession;

    /** @var Logger */
    private $suiteLogger;

    /** @var Helper */
    private $suiteHelper;

    /** @var OrderLoader */
    private $orderLoader;

    /** @var EcommerceManagement */
    private $ecommerceManagement;

    /** @var RecoverCart */
    private $recoverCart;

    /** @var CustomerUrl */
    private $customerUrl;

    /**
     * PiSuccess constructor.
     * @param Context $context
     * @param MsPayment $msPayment
     * @param Multishipping $multishipping
     * @param State $state
     * @param PageFactory $resultPageFactory
     * @param Session $checkoutSession
     * @param ThreeDSecure $block
     * @param Generic $session
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerSession $customerSession
     * @param Logger $suiteLogger
     * @param Data $suiteHelper
     * @param OrderLoader $orderLoader
     * @param EcommerceManagement $ecommerceManagement
     * @param RecoverCart $recoverCart
     * @param CustomerUrl $customerUrl
     */
    public function __construct(
        Context $context,
        MsPayment $msPayment,
        Multishipping $multishipping,
        State $state,
        PageFactory $resultPageFactory,
        Session $checkoutSession,
        ThreeDSecure $block,
        Generic $session,
        CustomerRepositoryInterface $customerRepository,
        CustomerSession $customerSession,
        Logger $suiteLogger,
        Data $suiteHelper,
        OrderLoader $orderLoader,
        EcommerceManagement $ecommerceManagement,
        RecoverCart $recoverCart,
        CustomerUrl $customerUrl
    ) {
        $this->msPayment = $msPayment;
        $this->multishipping = $multishipping;
        $this->state = $state;
        $this->resultPageFactory = $resultPageFactory;
        $this->checkoutSession = $checkoutSession;
        $this->block = $block;
        $this->session = $session;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->suiteLogger = $suiteLogger;
        $this->suiteHelper = $suiteHelper;
        $this->orderLoader = $orderLoader;
        $this->ecommerceManagement = $ecommerceManagement;
        $this->recoverCart = $recoverCart;
        $this->customerUrl  = $customerUrl;

        parent::__construct($context, $state, $multishipping);
    }

    public function execute()
    {
        $ids = $this->multishipping->getOrderIds();

        if (!empty($ids)) {
            $firstOrder = $this->orderLoader->loadOrderById(reset($ids));
            $paymentMethodCode = $firstOrder->getPayment()->getMethod();

            if (!$this->suiteHelper->methodCodeIsSagePay($paymentMethodCode)) {
                return parent::execute();
            }
        }

        if (!$this->customerSession->isLoggedIn()) {
            $this->getMessageManager()->addErrorMessage(__('Something went wrong, customer not logged in.'));
            return $this->redirect($this->customerUrl->getLoginUrl());
        }

        $status = $this->getRequest()->getParam('status');
        $result = null;

        if ($status == null) {
            $result = $this->msPayment->placeTransactions($ids);
            $status = $result->getStatusCode();
        }

        if ($status == \Ebizmarts\SagePaySuite\Model\Config::SUCCESS_STATUS) {
            $this->state->setCompleteStep(State::STEP_OVERVIEW);
            $orderIds = $this->getOrderIds($ids);
            $this->session->setOrderIds($orderIds);
            parent::execute();
        } elseif ($status == Config::AUTH3D_V2_REQUIRED_STATUS) {
            $acsUrl = $result->getAcsUrl();
            $vpstxid = $result->getTransactionId();
            $creq = $result->getCReq();

            $this->prepareThreeDBlock($vpstxid, $acsUrl, $ids, $creq);

            $this->setBlockBody();
        } else {
            $this->recoverCart->setShouldCancelOrders(true)->execute($this->getOrderIds($ids));
            if ($result != null && $result->getStatusDetail() != null) {
                $this->getMessageManager()->addErrorMessage(__($result->getStatusDetail()));
            } else {
                $this->getMessageManager()->addErrorMessage(__('Something went wrong, order not available.'));
            }
            $this->redirect('checkout/cart');
        }
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getOrderIds($ids = [])
    {
        $orderIds = [];

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $order = $this->orderLoader->loadOrderById($id);
                $this->checkIfOrderBelongsCustomerLoggedIn($order);
                $orderNumber = $order->getIncrementId();
                $orderIds[$id] = $orderNumber;
            }
        } else {
            $count = 0;
            while ($this->getRequest()->getParam('orderId' . $count) != null) {
                $id = (int)$this->getRequest()->getParam('orderId' . $count);
                $order = $this->orderLoader->loadOrderById($id);
                if ($order === null) {
                    $this->getMessageManager()->addErrorMessage('Something went wrong, order not available.');
                    $this->getMessageManager()->getMessages();
                    $this->redirect('checkout/cart');
                } else {
                    $this->checkIfOrderBelongsCustomerLoggedIn($order);
                    $orderNumber = $order->getIncrementId();
                    $orderIds[$id] = $orderNumber;
                    $count++;
                }
            }
        }

        return $orderIds;
    }

    /**
     * @param OrderInterface $order
     */
    private function checkIfOrderBelongsCustomerLoggedIn($order)
    {
        $customerId = $order->getCustomerId();
        $sessionCustomerId = $this->customerSession->getCustomer()->getId();

        try {
            if ($customerId !== $sessionCustomerId) {
                $this->getMessageManager()->addErrorMessage('The order does not belongs to the customer logged in');
                return $this->redirect('checkout/cart');
            }
        } catch (LocalizedException $e) {
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $e->getMessage(), [__METHOD__, __LINE__]);
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $e->getTraceAsString(), [__METHOD__, __LINE__]);
        }
    }

    /**
     * @param string $vpstxid
     * @param string $acsUrl
     * @param array $orderIds
     * @param string $data
     */
    private function prepareThreeDBlock($vpstxid, $acsUrl, $orderIds, $data)
    {
        $this->block->setMd($vpstxid);
        $this->block->setAscUrl($acsUrl);
        $this->block->setOrderIds($orderIds);

        $this->block->setCreq($data);

        $this->block->setTemplate('Ebizmarts_SagePaySuite::multishipping/3dv2-form.phtml');
    }

    public function setBlockBody()
    {
        $this->getResponse()
            ->setBody($this->block->toHtml());
    }

    public function getMessageManager()
    {
        return $this->messageManager;
    }

    /**
     * Set redirect into response
     *
     * @param   string $path
     * @param   array $arguments
     * @return  ResponseInterface
     */
    public function redirect($path, $arguments = [])
    {
        //phpcs:disable
        $this->_redirect->redirect($this->getResponse(), $path, $arguments);
        //phpcs:enable
        return $this->getResponse();
    }
}
