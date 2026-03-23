<?php

namespace Ebizmarts\SagePaySuite\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Ebizmarts\SagePaySuite\Model\ResourceModel\Token\CollectionFactory as TokenCollectionFactory;
use Ebizmarts\SagePaySuite\Model\Token\Save as SaveToken;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory as VaultCollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;

class MigrateTokens implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var TokenCollectionFactory
     */
    private $tokenCollectionFactory;
    /**
     * @var Json
     */
    private $serializer;
    /**
     * @var VaultCollectionFactory
     */
    private $vaultCollectionFactory;
    /**
     * @var SaveToken
     */
    private $saveToken;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $tokenRepository;
    private const SERVER_INTEGRATION = 'sagepaysuiteserver';
    private const PI_INTEGRATION     = 'sagepaysuitepi';

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param TokenCollectionFactory $tokenCollectionFactory
     * @param VaultCollectionFactory $vaultCollectionFactory
     * @param Json $serializer
     * @param SaveToken $saveToken
     * @param Logger $logger
     * @param PaymentTokenRepositoryInterface $tokenRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        TokenCollectionFactory $tokenCollectionFactory,
        VaultCollectionFactory $vaultCollectionFactory,
        Json $serializer,
        SaveToken $saveToken,
        Logger $logger,
        PaymentTokenRepositoryInterface $tokenRepository
    ) {
        $this->moduleDataSetup          = $moduleDataSetup;
        $this->tokenCollectionFactory   = $tokenCollectionFactory;
        $this->vaultCollectionFactory   = $vaultCollectionFactory;
        $this->serializer               = $serializer;
        $this->saveToken                = $saveToken;
        $this->logger                   = $logger;
        $this->tokenRepository          = $tokenRepository;
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
        $this->moduleDataSetup->startSetup();
        // migrate the server tokens
        $tokenCollection = $this->tokenCollectionFactory->create();
        $tokenCollection->addFieldToFilter('payment_method', ['eq'=>$this::SERVER_INTEGRATION]);
        $tokenCollection->addFieldToFilter('migrated', ['eq'=>0]);
        foreach ($tokenCollection as $token) {
            try {
                $this->saveToken->saveMovedToken($token, $this::SERVER_INTEGRATION);
                $token->setMigrated(1);
                $token->save();
            } catch (\Exception $e) {
                $this->logger->sageLog(Logger::LOG_EXCEPTION, __('Token already in vault'), [__LINE__, __METHOD__]);
            }
        }
        $tokenCollection->clear();
        // migrate the pi tokens
        $tokenCollection = $this->tokenCollectionFactory->create();
        $tokenCollection->addFieldToFilter('payment_method', ['eq'=>$this::PI_INTEGRATION]);
        $tokenCollection->addFieldToFilter('migrated', ['eq'=>0]);
        foreach ($tokenCollection as $token) {
            try {
                $sageToken = $this->tokenRepository->getById($token->getVaultId());
                $sageToken->setData('sagepaysuite_vendorname', $token->getVendorname());
                $sageToken->save();
                $token->setMigrated(1);
                $token->save();
            } catch (\Exception $e) {
                $this->logger->sageLog(Logger::LOG_EXCEPTION, $e->getMessage(), [__LINE__, __METHOD__]);
            }
        }
        $vaultCollection = $this->vaultCollectionFactory->create();
        foreach ($vaultCollection as $vault) {
            $details = $this->serializer->unserialize($vault->getDetails());
            $details = str_replace("\\", "", $this->serializer->serialize($details));
            $vault->setDetails($details);
            $vault->save();
        }
    }
}
