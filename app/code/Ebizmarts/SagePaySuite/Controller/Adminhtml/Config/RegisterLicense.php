<?php

namespace Ebizmarts\SagePaySuite\Controller\Adminhtml\Config;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\Config\ModuleVersion;
use Magento\Framework\HTTP\Adapter\Curl;
use Ebizmarts\SagePaySuite\Helper\Data as SageHelper;
use Laminas\Http\Request as HttpRequest;

class RegisterLicense implements HttpGetActionInterface
{
    /** @var JsonFactory */
    private $jsonFactory;

    /** @var Config */
    private $config;

    /** @var ModuleVersion */
    private $moduleVersion;

    /** @var Curl */
    private $curl;

    /** @var SageHelper */
    private $sageHelper;

    /** @var RequestInterface */
    private $request;

    /**
     * @param JsonFactory $jsonFactory
     * @param Config $config
     * @param ModuleVersion $moduleVersion
     * @param Curl $curl
     * @param SageHelper $sageHelper
     * @param RequestInterface $request
     */
    public function __construct(
        JsonFactory $jsonFactory,
        Config $config,
        ModuleVersion $moduleVersion,
        Curl $curl,
        SageHelper $sageHelper,
        RequestInterface $request
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->config = $config;
        $this->moduleVersion = $moduleVersion;
        $this->curl = $curl;
        $this->sageHelper = $sageHelper;
        $this->request = $request;
    }

    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $params = $this->getRequest()->getParams();
        $scope = $params['scope'];
        $scopeid = $params['scopeId'];
        $name = key_exists('name', $params) ? $params['name'] : '';
        $email = key_exists('email', $params) ? $params['email'] : '';
        $phone_code = key_exists('phone_code', $params) ? $params['phone_code'] : '';
        $phone_number = key_exists('phone_number', $params) ? $params['phone_number'] : '';

        $license = '';

        if (key_exists('license', $params)) {
            $license = $params['license'];
        }
        if (trim($license)=='') {
            $valid =0;
            $message = 'Invalid licence, please enter a valid license first';
            return $resultJson->setData([
                'valid' => (int)$valid,
                'message' => $message,
            ]);
        }
        if (!$this->sageHelper->validateLicense($license, $scope, $scopeid)) {
            $valid =0;
            $message = 'Invalid licence, please enter a valid license first';
            return $resultJson->setData([
                'valid' => (int)$valid,
                'message' => $message,
            ]);
        }
        if (key_exists('vendor', $params)) {
            $vendor = $params['vendor'];
        } else {
            $vendor = '';
        }
        if (trim($vendor)=='') {
            $valid =0;
            $message = 'You must enter a Vendorname first';
            return $resultJson->setData([
                'valid' => (int)$valid,
                'message' => $message,
            ]);
        }
        if ($vendor=='testebizmarts') {
            $valid =0;
            $message = 'Please enter your own Vendorname first';
            return $resultJson->setData([
                'valid' => (int)$valid,
                'message' => $message,
            ]);
        }
        if ($name == '') {
            $valid =0;
            $message = 'Invalid billing name, please enter a valid billing name first';
            return $resultJson->setData([
                'valid' => (int)$valid,
                'message' => $message,
            ]);
        }
        if ($email == '' || !$this->sageHelper->validateEmailAddress($email)) {
            $valid =0;
            $message = 'Invalid billing email, please enter a valid billing email first';
            return $resultJson->setData([
                'valid' => (int)$valid,
                'message' => $message,
            ]);
        }
        if ($phone_number == '') {
            $valid =0;
            $message = 'Invalid billing phone, please enter a valid billing phone first';
            return $resultJson->setData([
                'valid' => (int)$valid,
                'message' => $message,
            ]);
        } else {
            if (!$this->sageHelper->validatePhoneNumber($phone_code, $phone_number)) {
                $valid = 0;
                $message = __('Please enter a valid phone number.');
                return $resultJson->setData([
                    'valid' => (int)$valid,
                    'message' => $message,
                ]);
            }
        }
        $valid = 1;
        $message = 'Thanks for registering your license';
        $phone = '+' . $phone_code . ' '. $phone_number;

        $this->config->setConfigurationScope($scope);
        $moduleVersion = $this->moduleVersion->getModuleVersion('Ebizmarts_SagePaySuite');
        // TODO connecto to the store
        $post = $this->postStore($license, $moduleVersion, $vendor, $name, $email, $phone);

        if (key_exists('error', $post) && $post['error']==0) {
            if ($scope == 'store') {
                $scope = 'stores';
            } elseif ($scope == 'website') {
                $scope = 'websites';
            }
            $this->config->saveConfigValue("sagepaysuite/global/register", $moduleVersion, $scopeid, $scope);
            $this->config->saveConfigValue("sagepaysuite/global/registervendor", $vendor, $scopeid, $scope);
            $this->config->saveConfigValue("sagepaysuite/global/name", $name, $scopeid, $scope);
            $this->config->saveConfigValue("sagepaysuite/global/email", $email, $scopeid, $scope);
            $this->config->saveConfigValue("sagepaysuite/global/phone_code", $phone_code, $scopeid, $scope);
            $this->config->saveConfigValue("sagepaysuite/global/phone_number", $phone_number, $scopeid, $scope);
        } else {
            $valid = 0;
            $message = key_exists('message', $post) ? $post['message'] : 'Error activating your license';
        }
        return $resultJson->setData([
            'valid' => (int)$valid,
            'message' => $message,
        ]);
    }

    /**
     * @param $license
     * @param $version
     * @return array
     */
    protected function postStore($license, $version, $vendor, $name, $email, $phone)
    {
        $this->initializeCurl();
        $postData = [];
        $postData['license'] = $license;
        $postData['version'] = $version;
        $postData['domain'] = $this->config->getStoreDomain();
        $postData['vendor'] = $vendor;
        $postData['name'] = $name;
        $postData['email'] = $email;
        $postData['phone'] = $phone;
        $data = json_encode($postData);
        $url = Config::URL_CHECK_LICENSE.$license;
        // @codingStandardsIgnoreStart
        $this->curl->write(
            HttpRequest::METHOD_PUT,
            $url,
            HttpRequest::VERSION_11,
            ['Content-type: application/json','Authorization: Bearer dfd50a43629d7dfd236adaa45982fd209dbd3d64'],
            $data
        );
        // @codingStandardsIgnoreEnd
        $responseData = $this->curl->read();
        $responseCode = $this->curl->getInfo(CURLINFO_HTTP_CODE);
        $this->curl->close();
        if ($responseCode==500) {
            return ['returnCode' => $responseCode,'reason' => 'Communication error'];
        }
        $responseData = $this->processResponse($responseData);
        return $responseData;
    }

    public function initializeCurl()
    {
        $config = [
            'timeout'    => 120,
            'verifyhost' => 2,
        ];

        $this->curl->setConfig($config);
    }

    /**
     * @param $response
     * @return array
     */
    public function processResponse($response)
    {
        $data = preg_split('/^\r?$/m', $response, 2);
        $dataRes = json_decode(trim($data[1]));

        return (array)$dataRes;
    }

    public function _isAllowed()
    {
        return true;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
