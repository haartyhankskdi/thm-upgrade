<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MY\CustomExport\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class Status
 */
class RegisteredGP extends Column 
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
        SearchCriteriaBuilder $criteria,
        array $components = [],
        array $data = []
    ) {        
        $this->_searchCriteria  = $criteria;
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

                if (!isset($item[$this->getData('name')]) && empty($item[$this->getData('name')])) {
                    
                        $item[$this->getData('name')] = 'No'; 
                }else{

                    $options = json_decode($item[$this->getData('name')], TRUE);                     
                    $item[$this->getData('name')] = 'Practice Name: '.$options['name_of_practice'].PHP_EOL.'Address: '.$options['address_line_one'].','.$options['address_line_two'].','.$options['city'].','.$options['county'].','.$options['postcode'];
                }    
            }
        }  
        
        return $dataSource;
    }
}
