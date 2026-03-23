<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Kdi\CustomExport\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class Status
 */
class MedicalStrength extends Column
{
    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CollectionFactory $collectionFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,        
        array $components = [],
        array $data = []
    ) {        
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
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
            	$options = json_decode($item['product_options'], TRUE);                   
            	if (isset($options['attributes_info']) && !empty($options['attributes_info'])) 
		        {             
			        foreach ($options['attributes_info'] as $option) 
			        {      
			        	if(strtolower($option['label']) == $this->getData('name')){

			        		$item[$this->getData('name')] = $option['value'];
			        	}
			        						              
			        }
		        }        	
            }
        }        
        return $dataSource;
    }
}
