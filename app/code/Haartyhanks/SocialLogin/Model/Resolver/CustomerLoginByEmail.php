<?php
namespace Haartyhanks\SocialLogin\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Integration\Model\Oauth\TokenFactory;

class CustomerLoginByEmail implements ResolverInterface
{
    private $customerRepository; 
    private $customerFactory;
    private $tokenFactory;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerFactory,
        TokenFactory $tokenFactory
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->tokenFactory = $tokenFactory;
    }

    public function resolve(
        $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['email'])) {
            throw new GraphQlInputException(__('Email is required.'));
        }

        $email = $args['email'];
        $firstname = $args['firstname'] ?? '';
        $lastname = $args['lastname'] ?? '';

        try {
            // Try to get existing customer
            $customer = $this->customerRepository->get($email);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // If customer doesn't exist, create a new one with proper name
            $customer = $this->customerFactory->create();
            $customer->setEmail($email);
            $customer->setFirstname($firstname);
            $customer->setLastname($lastname);
            $customer->setWebsiteId(1);
            $this->customerRepository->save($customer);
        }

        // Generate token
        $tokenModel = $this->tokenFactory->create();
        $tokenModel->createCustomerToken($customer->getId());

        return [
            'token' => $tokenModel->getToken()
        ];
    }
}
