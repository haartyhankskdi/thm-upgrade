<?php

namespace Ebizmarts\BrippoPayments\Plugin\Order;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Sales\Ui\Component\Listing\Column\ViewAction;

class ListingActions
{
    protected $context;
    protected $urlBuilder;
    protected $logger;

    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        Logger $logger
    ) {
        $this->context = $context;
        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;
    }
    public function afterPrepareDataSource(
        ViewAction $subject,
        array $dataSource
    ) {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $urlEntityParamName = $subject->getData('config/urlEntityParamName') ?: 'entity_id';
                if ((empty($item['total_paid']) || $item['total_paid'] == 0)
                    && (isset($item['status']) && $item['status'] !== BrippoOrder::STATUS_AUTHORIZED)) {
                    $item[$subject->getData('name')]['recover'] = [
                        'href' => $this->urlBuilder->getUrl(
                            "sales/order/view",
                            [
                                $urlEntityParamName => $item['entity_id'],
                                'brippoRecoverOnload' => true
                            ]
                        ),
                        'label' => __('Recover with Brippo')
                    ];
                }
            }
        }

        return $dataSource;
    }
}
