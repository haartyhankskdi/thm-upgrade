<?php

namespace Ebizmarts\BrippoPayments\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\StatusFactory;

class AddBrippoOrderStatusesVersion1 implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    private $statusFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param StatusFactory $statusFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        StatusFactory $statusFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->statusFactory = $statusFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /*
         * BRIPPO PENDING
         */
        $statusPending = $this->statusFactory->create();
        $statusPending->setData('status', 'brippo_pending')
            ->setData('label', 'Brippo Incomplete [Do NOT Ship]')
            ->save();
        $statusPending->assignState(Order::STATE_NEW, false, false);

        /*
         * BRIPPO AUTHORIZED
         */
        $statusAuthorized = $this->statusFactory->create();
        $statusAuthorized->setData('status', 'brippo_authorized')
            ->setData('label', 'Brippo Authorized')
            ->save();
        $statusAuthorized->assignState(Order::STATE_PENDING_PAYMENT, false, true);

        /*
         * BRIPPO PAYMENT FAILED
         */
        $statusPaymentFailed = $this->statusFactory->create();
        $statusPaymentFailed->setData('status', 'brippo_payment_failed')
            ->setData('label', 'Brippo Payment Failed')
            ->save();
        $statusPaymentFailed->assignState(Order::STATE_CANCELED, false, true);

        /*
         * BRIPPO GATEWAY ERROR
         */
        $statusGatewayError = $this->statusFactory->create();
        $statusGatewayError->setData('status', 'brippo_gateway_error')
            ->setData('label', 'Brippo Gateway Error')
            ->save();
        $statusGatewayError->assignState(Order::STATE_CANCELED, false, true);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
