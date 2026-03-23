<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Crypt
 * @package Mageplaza\RewardPointsUltimate\Helper
 */
class Crypt extends AbstractHelper
{
    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Crypt constructor.
     *
     * @param Context $context
     * @param EncryptorInterface $encryptor
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->encryptor = $encryptor;
        $this->customerRepository = $customerRepository;

        parent::__construct($context);
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function decrypt($data)
    {
        return str_replace("\x0", '', trim($this->encryptor->decrypt(base64_decode((string)$data))));
    }

    /**
     * @param $data
     *
     * @return string
     */
    public function encrypt($data)
    {
        return base64_encode($this->encryptor->encrypt((string)$data));
    }

    /**
     * @param $referCodeOrEmail
     *
     * @return bool|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function checkReferCodeOrEmail($referCodeOrEmail)
    {
        $isEmail = '';
        if ($referCodeOrEmail) {
            if (filter_var($referCodeOrEmail, FILTER_VALIDATE_EMAIL)) {
                $customer = $this->customerRepository->get($referCodeOrEmail);
                $isEmail = true;
            } else {
                $customer = $this->customerRepository->getById($this->decrypt($referCodeOrEmail));
            }
            if ($customer->getId()) {
                return $isEmail ? $this->encrypt($customer->getId()) : $referCodeOrEmail;
            }
        }

        return false;
    }
}
