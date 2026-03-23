<?php

namespace Ebizmarts\SagePaySuite\Model\Token;

use Ebizmarts\SagePaySuite\Api\Data\ResultInterface;
use Ebizmarts\SagePaySuite\Helper\RepositoryQuery;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;

class Get implements \Ebizmarts\SagePaySuite\Api\TokenGetInterface
{
    /** @var Logger */
    private $suiteLogger;

    /** @var Json */
    private $jsonSerializer;

    /** @var ResultInterface */
    private $result;

    /** @var PaymentTokenRepositoryInterface */
    private $paymentTokenRepository;

    /** @var RepositoryQuery */
    private $repositoryQuery;

    /**
     * VaultDetailsHandler constructor.
     * @param Logger $suiteLogger
     * @param Json $jsonSerializer
     * @param ResultInterface $result
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param RepositoryQuery $repositoryQuery
     */
    public function __construct(
        Logger $suiteLogger,
        Json $jsonSerializer,
        ResultInterface $result,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        RepositoryQuery $repositoryQuery
    ) {
        $this->suiteLogger            = $suiteLogger;
        $this->jsonSerializer         = $jsonSerializer;
        $this->result                 = $result;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->repositoryQuery        = $repositoryQuery;
    }

    /**
     * @param $tokenId
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface
     */
    public function getTokenById($tokenId)
    {
        return $this->paymentTokenRepository->getById($tokenId);
    }

    /**
     * @param $customerId
     * @param $method
     * @param $vendor
     * @return \Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface|\Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface[]
     */
    public function getTokensFromCustomer($customerId, $method, $vendor)
    {
        $searchCriteria = $this->createSearchCriteria($customerId, $method, $vendor);
        //getList returns an array instead of a single object as Magento's doc says
        return $this->paymentTokenRepository->getList($searchCriteria);
    }

    /**
     * @param $customerId
     * @param $method
     * @param $vendor
     * @return array
     */
    public function getTokensFromCustomerToShowOnGrid($customerId, $method, $vendor)
    {
        $tokenList = $this->getTokensFromCustomer($customerId, $method, $vendor);
        $tokenListToShow = [];
        foreach ($tokenList->getItems() as $token) {
            if ($token->getIsActive() && $token->getIsVisible()) {
                $tokenDetails = $this->convertJsonToArray($token->getTokenDetails());
                $data = [
                    'id' => $token->getEntityId(),
                    'customer_id' => $token->getCustomerId(),
                    'cc_last_4' => $tokenDetails['maskedCC'],
                    'cc_type' => $tokenDetails['type'],
                    'cc_exp_month' => substr($tokenDetails['expirationDate'], 0, 2),
                    'cc_exp_year' => substr($tokenDetails['expirationDate'], 3, 2),
                    'token' => $token->getGatewayToken()
                ];
                $tokenListToShow[] = $data;
            }
        }

        return $tokenListToShow;
    }

    /**
     * @param string $tokenId
     * @return string
     */
    public function getSagePayToken($tokenId)
    {
        $token = $this->paymentTokenRepository->getById($tokenId);
        return $token->getGatewayToken();
    }

    /**
     * @param string $tokenId
     * @return ResultInterface
     */
    public function getSagePayTokenAsResultInterface($tokenId)
    {
        $token = $this->getSagePayToken($tokenId);
        if (empty($token)) {
            $this->result->setSuccess(false);
        } else {
            $this->result->setSuccess(true);
            $this->result->setResponse($token);
        }

        return $this->result;
    }

    /**
     * @param string $string
     * @return array
     */
    private function convertJsonToArray($string)
    {
        return $this->jsonSerializer->unserialize($string);
    }

    /**
     * @param $customerId
     * @param $method
     * @return \Magento\Framework\Api\SearchCriteria
     */
    private function createSearchCriteria($customerId, $method, $vendor)
    {
        $customerIdFilter = [
            [
                'field' => 'customer_id',
                'conditionType' => 'eq',
                'value' => $customerId
            ],
            [
                'field' => 'payment_method_code',
                'conditionType' => 'like',
                'value' => "$method%"
            ],
            [
                'field' => 'sagepaysuite_vendorname',
                'conditionType' => 'eq',
                'value' => $vendor
            ]
        ];

        $searchCriteria = $this->repositoryQuery->buildSearchCriteriaWithAND($customerIdFilter);
        return $searchCriteria;
    }
}
