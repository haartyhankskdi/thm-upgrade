<?php

namespace Ebizmarts\SagePaySuite\Ui\Component\Grid\Fraud\Column;

use Ebizmarts\SagePaySuite\Helper\AdditionalInformation;
use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\View\Asset\Repository as UrlFile;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Fraud extends Column
{
    /** @var UrlInterface $urlInterface */
    private $urlInterface;

    /** @var UrlFile $urlFile */
    private $urlFile;

    /** @var AdditionalInformation $additionalInformation */
    private $additionalInformation;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlInterface
     * @param UrlFile $urlFile
     * @param AdditionalInformation $additionalInformation
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlInterface,
        UrlFile $urlFile,
        AdditionalInformation $additionalInformation,
        array $components = [],
        array $data = []
    ) {
        $this->urlInterface = $urlInterface;
        $this->urlFile = $urlFile;
        $this->additionalInformation = $additionalInformation;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['order_id'])) {
                    $link = $this->urlInterface->getUrl(
                        'sales/order/view/',
                        [ 'order_id' => $item['order_id']]
                    );
                    $item['increment_id'] =
                        '<a href="' . $link . '" target="_blank">' . $item['increment_id'] . '</a>';
                }
                if (isset($item['transaction_id'])) {
                    $link = $this->urlInterface->getUrl(
                        'sales/transactions/view/',
                        [ 'txn_id' => $item['transaction_id']]
                    );
                    $item['transaction_id'] =
                        '<a href="' . $link . '" target="_blank">' . $item['transaction_id'] . '</a>';
                }
                $item['recommendation'] = $this->getRecommendation($item['recommendation']);
                $item['provider'] = $this->getFraudProvider($item['provider']);
                $dataRules = !empty($item['rules'])
                    ? $this->additionalInformation->getUnserializedData($item['rules'])
                    : null;
                $item['rules'] = $this->processRules($dataRules);
            }
        }
        return $dataSource;
    }

    /**
     * @param string $html
     * @return string
     */
    private function getRecommendation($html)
    {
        switch ($html) {
            case Config::REDSTATUS_CHALLENGE:
            case Config::T3STATUS_HOLD:
                $html = '<span style="color:orange;">' . $html . '</span>';
                break;
            case Config::REDSTATUS_DENY:
            case Config::T3STATUS_REJECT:
                $html = '<span style="color:red;">' . $html . '</span>';
                break;
        }

        return $html;
    }

    /**
     * @param string $provider
     * @return string
     */
    private function getFraudProvider($provider)
    {
        if ($provider === "ReD") {
            $html = '<img style="height: 20px;" src="';
            $html .= $this->getFraudProviderLogo('red') . '">';
        } else {
            $html =
                '<span>
            <img style="height: 20px;vertical-align: text-top;" src="'.$this->getFraudProviderLogo('t3m').'">
                T3M
        </span>';
        }

        return $html;
    }

    /**
     * @param string $name
     * @return mixed
     */
    private function getFraudProviderLogo($name)
    {
        return $this->urlFile->getUrl('Ebizmarts_SagePaySuite::images/' . $name . '_logo.png');
    }

    /**
     * @param $rules
     * @return string
     */
    private function processRules($rules)
    {
        if (empty($rules)) {
            return $rules;
        }

        if (!\is_array($rules)) {
            return $rules;
        }

        return $this->processMultipleRulesData($rules);
    }

    /**
     * @param $rules
     * @return string
     */
    private function processMultipleRulesData($rules)
    {
        $return = '<ul>';
        foreach ($rules as $rule) {
            $description = $rule['description'] ?? '';
            $score = $rule['score'] ?? '';
            $return .= __('<li>%1 <strong>(score: %2)</strong></li>', $description, $score);
        }
        $return .= '</ul>';

        return $return;
    }
}
