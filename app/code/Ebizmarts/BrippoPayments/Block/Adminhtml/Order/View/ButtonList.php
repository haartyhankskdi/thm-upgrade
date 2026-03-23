<?php
namespace Ebizmarts\BrippoPayments\Block\Adminhtml\Order\View;

use Ebizmarts\BrippoPayments\Helper\Data;
use Ebizmarts\BrippoPayments\Helper\RecoverCheckout;
use Magento\Backend\Block\Widget\Button\ItemFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class ButtonList extends \Magento\Backend\Block\Widget\Button\ButtonList
{
    protected $request;
    protected $orderRepository;
    protected $dataHelper;
    protected $recoverCheckoutHelper;

    /**
     * @param ItemFactory $itemFactory
     * @param RequestInterface $request
     * @param OrderRepositoryInterface $orderRepository
     * @param Data $dataHelper
     * @param RecoverCheckout $recoverCheckoutHelper
     * @throws NoSuchEntityException
     */
    public function __construct(
        ItemFactory $itemFactory,
        RequestInterface $request,
        OrderRepositoryInterface $orderRepository,
        Data $dataHelper,
        RecoverCheckout $recoverCheckoutHelper
    ) {
        parent::__construct($itemFactory);
        $this->request = $request;
        $this->orderRepository = $orderRepository;
        $this->dataHelper = $dataHelper;
        $this->recoverCheckoutHelper = $recoverCheckoutHelper;

        $orderId = $this->request->getParam('order_id');
        if (!empty($orderId)) {
            $order = $this->orderRepository->get($orderId);
            if (!empty($order) && !empty($order->getEntityId())) {
                if ($order->getState() === Order::STATE_CANCELED
                    || $order->getState() === Order::STATE_NEW
                    || $order->getState() === Order::STATE_HOLDED
                    || $order->getState() === Order::STATE_PENDING_PAYMENT) {
                    $customerPhone = '';
                    if (!empty($order->getBillingAddress()->getTelephone())) {
                        $customerPhone = $order->getBillingAddress()->getTelephone();
                    }

                    $recoverLink = $this->recoverCheckoutHelper->getRecoverLink($order, RecoverCheckout::LINK_SOURCE_MANUAL, true);

                    $this->add('brippo_recover_order', [
                        'label' => __('Recover with Brippo'),
                        'on_click' =>
                            'BrippoAdmin.recoverOrder.show('
                            . '\'' . $orderId . '\','
                            . '\'' . $order->getIncrementId() . '\','
                            . '\'' . $order->getCustomerEmail() . '\','
                            . '\'' . $order->getCustomerFirstname() . '\','
                            . '\'' . $this->dataHelper->getStoreNameFromOrder($order) . '\','
                            . '\'' . $customerPhone . '\','
                            . '\'' . $recoverLink . '\''
                            . ');'
                    ]);
                }
            }
        }
    }
}
