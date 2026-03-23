<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Haartyhanks\Career\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;

class AttachmentActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected $urlBuilder;

    const UPLOADED_DIR = "career_uploads/";

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
       \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
      \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
     \Magento\Store\Model\StoreManagerInterface $storeManager,
    array $components = [],
    array $data = []
)
{
    $this->_storeManager = $storeManager;
    parent::__construct($context, $uiComponentFactory, $components, $data);
}


    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $mediaUrl = $this ->_storeManager-> getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['attachment'])) {
                    /* $item[$this->getData('name')] = html_entity_decode('<a download href="'. $mediaUrl . self::UPLOADED_DIR . $item['attachment'] .  '">'. $item['attachment'].'</a>'); */

                     $item[$this->getData('name')] = [
                        'view' => [
                            'href' => $mediaUrl . self::UPLOADED_DIR . $item['attachment'],
                            'label' => __($item['attachment']),
                            'target' => '_blank'
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}

