<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Kdi\CustomExport\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class Status
 */
class CustomerGender extends Column
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

                if (isset($item[$this->getData('name')]) && !empty($item[$this->getData('name')])) {
                    
                        $item[$this->getData('name')] = ($item[$this->getData('name')] == 1) ? 'Male' : 'Female'; 
                }    
            }
        }  
        
        return $dataSource;
    }
}
