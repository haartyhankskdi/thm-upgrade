<?php

namespace Ebizmarts\SagePaySuite\Controller\Multishipping;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;


use Ebizmarts\SagePaySuite\Model\ResourceModel\MsScaData;
use Ebizmarts\SagePaySuite\Model\MsScaData as ScaModel;

class ScaRequestData implements ActionInterface
{
    /** @var MsScaData */
    private $msScaResourceModel;

    /** @var ScaModel */
    private $msScaModel;

    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @param MsScaData $msScaResourceModel
     * @param ScaModel $msScaModel
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(
        MsScaData $msScaResourceModel,
        ScaModel $msScaModel,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $this->msScaResourceModel = $msScaResourceModel;
        $this->msScaModel         = $msScaModel;
        $this->request            = $request;
        $this->response           = $response;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $scaParams = $this->getRequest()->getParams();
        if (isset($scaParams)) {
            $jsEnabled = $scaParams['javascript_enabled'];
            $javaEnabled = $scaParams['java_enabled'];
            $colorDepth = $scaParams['color_depth'];
            $screenHeight = $scaParams['screen_height'];
            $screenWidth = $scaParams['screen_width'];
            $timeZone = $scaParams['timezone'];
            $acceptHeaders = $scaParams['accept_headers'];
            $language = $scaParams['language'];
            $userAgent = $scaParams['user_agent'];

            $this->msScaModel->setJsEnabled($jsEnabled);
            $this->msScaModel->setJavaEnabled($javaEnabled);
            $this->msScaModel->setColorDepth($colorDepth);
            $this->msScaModel->setScreenHeight($screenHeight);
            $this->msScaModel->setScreenWidth($screenWidth);
            $this->msScaModel->setTimeZone($timeZone);
            $this->msScaModel->setAcceptHeaders($acceptHeaders);
            $this->msScaModel->setLanguage($language);
            $this->msScaModel->setUserAgent($userAgent);
            $this->msScaModel->save();
        }
        return $this->response;
    }

    protected function getRequest()
    {
        return $this->request;
    }
}
