<?php

namespace Ebizmarts\SagePaySuite\Ui\Component\Grid\Token\Column;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Tokens extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    private $ccTypesMap = [
        'VI'    => 'Visa',
        'MC'    => 'MasterCard',
        'MI'    => 'Maestro',
        'AE'    => 'AmericanExpress',
        'DN'    => 'Diners',
        'JCB'   => 'JCB'
    ];

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = [
                    'delete' => [
                        'href' => $this->urlBuilder->getUrl(
                            'sagepaysuite/reports_tokens/delete',
                            [
                                'id' => $item['entity_id'],
                                'customer_id' => $item['customer_id'],
                                '_nosid' => true,
                                '_secure' => true
                            ]
                        ),
                        'label' => 'Delete'
                    ]
                ];
            }
        }
        return $dataSource;
    }
    private function getCcType($cctype)
    {
        if (key_exists($cctype, $this->ccTypesMap)) {
            return $this->ccTypesMap[$cctype];
        }
        return $cctype;
    }
}
