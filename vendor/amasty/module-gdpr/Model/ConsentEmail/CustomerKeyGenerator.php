<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\ConsentEmail;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Encryption\EncryptorInterface;

class CustomerKeyGenerator
{
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    public function __construct(
        EncryptorInterface $encryptor,
        DeploymentConfig $deploymentConfig
    ) {
        $this->encryptor = $encryptor;
        $this->deploymentConfig = $deploymentConfig;
    }

    public function generateKey(int $customerId, string $policyVersion): string
    {
        $salt = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);

        return $this->encryptor->getHash($customerId . ':' . $policyVersion, $salt);
    }
}
