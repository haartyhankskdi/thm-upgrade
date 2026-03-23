<?php

namespace Ebizmarts\SagePaySuite\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;

class AddColumnSagepayToken implements SchemaPatchInterface
{
    /** @var ModuleDataSetupInterface $moduleDataSetup */
    private $moduleDataSetup;

    /**
     * AddColumnSagepayToken constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }
    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        // update data
        $this->moduleDataSetup->startSetup();
        $tableNameData = $this->moduleDataSetup->getTable('vault_payment_token');
        // phpcs:disable
        $results = $this->moduleDataSetup->getConnection()->query(
            "SELECT * FROM " . $tableNameData . " WHERE payment_method_code = 'sagepaysuitepi' AND is_active = 1"
        )->fetchAll();
        // phpcs:enable
        $sagepayTokenTable = $this->moduleDataSetup->getTable('sagepaysuite_token');
        $configTableName = $this->moduleDataSetup->getTable('core_config_data');
        // phpcs:disable
        $vendorname = $this->moduleDataSetup->getConnection()->query(
            "SELECT value FROM " . $configTableName . " WHERE path = 'sagepaysuite/global/vendorname'"
        )->fetch();
        // phpcs:enable
        /** @var PaymentTokenInterface $result */
        foreach ($results as $result) {
            $data = $this->getData($result, $vendorname);
            $this->moduleDataSetup->getConnection()->insert(
                $sagepayTokenTable,
                $data
            );
        }

        $this->moduleDataSetup->endSetup();
        // end update data
    }

    /**
     * @param $result
     * @return []
     */
    private function getData($paymentToken, $vendorName)
    {
        $data = [];
        $paymentTokenDetails = isset($paymentToken['details']) ? $paymentToken['details'] : [];
        if ($paymentTokenDetails !== null) {
            $details = json_decode($paymentTokenDetails);
            $cardType = property_exists($details, 'type') ? $details->type : '';
            $cardLastFour = property_exists($details, 'maskedCC') ? $details->maskedCC : '';
            $cardExpMonth = $this->getDetailDate($details, true);
            $cardExpYear = $this->getDetailDate($details);
            $data = [
                'customer_id' => $paymentToken['customer_id'],
                'token' => '{' . $paymentToken['gateway_token'] . '}',
                'cc_type' => $cardType,
                'cc_last_4' => $cardLastFour,
                'cc_exp_month' => $cardExpMonth,
                'cc_exp_year' => $cardExpYear,
                'vendorname' => isset($vendorName['value']) ? $vendorName['value'] : '',
                'created_at' => $paymentToken['created_at'],
                'payment_method' => $paymentToken['payment_method_code'],
                'vault_id' => $paymentToken['entity_id']
            ];
        }
        return $data;
    }

    /**
     * @param $details
     * @param $isMonth
     * @return string
     */
    private function getDetailDate($details, $isMonth = false)
    {
        return property_exists($details, 'expirationDate')
            ? $isMonth
                ? substr($details->expirationDate, 0, 2)
                : substr($details->expirationDate, 3)
            : '';
    }
}
