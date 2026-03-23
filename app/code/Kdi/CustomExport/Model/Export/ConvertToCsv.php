<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Kdi\CustomExport\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider;
use Kdi\CustomExport\Ui\Component\Listing\Column\Transaction;
use Kdi\CustomExport\Ui\Component\Listing\Column\TxCode;

/**
 * Class ConvertToCsv
 */
class ConvertToCsv extends \Magento\Ui\Model\Export\ConvertToCsv
{
    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var int|null
     */
    protected $pageSize = null;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var TxCode
     */
    protected $TxCode;

    /**
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param MetadataProvider $metadataProvider
     * @param int $pageSize
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        MetadataProvider $metadataProvider,
        Transaction $transaction,
        TxCode $TxCode,
        $pageSize = 2000
    ) {
        $this->filter = $filter;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->metadataProvider = $metadataProvider;
        $this->pageSize = $pageSize;
        $this->transaction = $transaction;
        $this->TxCode = $TxCode;
    }

    /**
     * Returns CSV file
     *
     * @return array
     * @throws LocalizedException
     */
    public function getCsvFile()
    {                
        $component = $this->filter->getComponent();

        $name = md5(microtime());
        $file = 'export/'. $component->getName() . $name . '.csv';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();
        $fields = $this->metadataProvider->getFields($component);
        $options = $this->metadataProvider->getOptions();

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->metadataProvider->getHeaders($component));
        $i = 1;
        $searchCriteria = $dataProvider->getSearchCriteria()
            ->setCurrentPage($i)
            ->setPageSize($this->pageSize);
        $totalCount = (int) $dataProvider->getSearchResult()->getTotalCount();
        while ($totalCount > 0) {

            $items = $dataProvider->getSearchResult()->getItems();

            foreach ($items as $item) {

                //echo "<pre>";var_dump($item['suffer_diagnosed_yes']);
                $entityId = $item->getEntityId(); 

                // Retrieve transaction data using getTransactionId() function
                $transactionData = $this->transaction->getTransactionId($entityId);
                $TxCode = $this->TxCode->getTxCode($entityId);

                // Save the transaction data back to the $item object or any other relevant location
                $item['transaction_id'] = $transactionData;
                $item['vendor_tx_code'] = $TxCode;

                $item['taxpercent'] = 0;                               

                if (isset($item['product_options']) && !empty($item['product_options'])){  

                    $options = json_decode($item['product_options'], TRUE);

                    if (isset($options['attributes_info']) && !empty($options['attributes_info'])){ 

                        foreach ($options['attributes_info'] as $option) {      

                            if(strtolower($option['label']) == 'size'){                                
                                $item['size'] = $option['value'];
                            }
                            if(strtolower($option['label']) == 'brand'){                                
                                $item['brand'] = $option['value'];
                            }
                            if(strtolower($option['label']) == 'medicine strength'){                                
                                $item['medicine strength'] = $option['value'];
                            }
                        }
                    }
                }

                switch($item['customer_gender']){
                    case 1 :
                        $item['customer_gender'] = 'Male';
                        break;
                    case 2:
                        $item['customer_gender'] = 'Female';
                        break;                    
                }

                switch($item['prescriber_name']){
                    case 1 :
                        $item['prescriber_name'] = 'Avi Test';
                        break;
                    case 2:
                        $item['prescriber_name'] = 'Gurdev Sehmi';
                        break; 
                    case 3:
                        $item['prescriber_name'] = 'Bob Rihal';
                        break;  
                    case 4:
                        $item['prescriber_name'] = 'Vip Patel';
                        break;                           
                    case 5:
                        $item['prescriber_name'] = 'Bez Hasanyan';
                        break;
                    case 6:
                        $item['prescriber_name'] = 'OTC Sale';
                        break; 
                    case 7:
                        $item['prescriber_name'] = 'School Order';
                        break;  
                    case 8:
                        $item['prescriber_name'] = 'Vet Rx Needed';
                        break;                           
                    case 9:
                        $item['prescriber_name'] = 'Amardeep Sehmi';
                        break;         
                }

                switch($item['customer_group_id']){
                    case 1 :
                        $item['customer_group_id'] = 'General';
                        break;
                    case 2:
                        $item['customer_group_id'] = 'Wholesale';
                        break; 
                    case 3:
                        $item['customer_group_id'] = 'Retailer';
                        break;  
                    case 4:
                        $item['customer_group_id'] = 'Suspect';
                        break;                               
                }

                switch($item['subscriber_status']){
                    case 1 :
                        $item['subscriber_status'] = 'Subscribed';
                        break;
                    case 2:
                        $item['subscriber_status'] = 'Not Activated';
                        break; 
                    case 3:
                        $item['subscriber_status'] = 'Unsubscribed';
                        break;  
                    case 4:
                        $item['subscriber_status'] = 'Unconfirmed';
                        break;                               
                }

                // if ($item['suffer_diagnosed_yes'] == null){                    

                //         $item['suffer_diagnosed_yes'] = 'No';
                // } 

                // if ($item['other_medication_yes'] == null){

                //         $item['other_medication_yes'] = 'No';
                // } 

                // if ($item['have_allergies_yes'] == null){

                //         $item['have_allergies_yes'] = 'No';
                // } 

                // if ($item['upload_documents_prescriber_yes'] == null){

                //         $item['upload_documents_prescriber_yes'] = 'No';
                // } 

                // if ($item['prescriber_to_know_yes'] == null){
                        
                //          $item['prescriber_to_know_yes'] = 'No';
                // }
                   
                // if($item['registered_gp'] != 0) {  
                //     if(isset($item['registered_gp_surgery']) && !empty($item['registered_gp_surgery']) && !is_array($item['registered_gp_surgery'])){

                //         $item['registered_gp_surgery'] = 'No';

                //     }else{                    
                //         $registeredGPOptions = json_decode($item['registered_gp_surgery'], TRUE);
                //         if(!empty($registeredGPOptions['name_of_practice']) && !empty($registeredGPOptions['address_line_one']) && !empty($registeredGPOptions['address_line_two']) && !empty($registeredGPOptions['city']) && !empty($registeredGPOptions['county']) && !empty($registeredGPOptions['postcode'])) {

                //                 $item['registered_gp_surgery'] = 'Practice Name: '.$registeredGPOptions['name_of_practice'].' '.'Address: '.$registeredGPOptions['address_line_one'].','.$registeredGPOptions['address_line_two'].','.$registeredGPOptions['city'].','.$registeredGPOptions['county'].','.$registeredGPOptions['postcode'];
                //         }else{
                //             $item['registered_gp_surgery'] = 'No';
                //         }
                //     }   
                // }else{
                //     $item['registered_gp_surgery'] = 'No';
                // }   

                // if($item['registered_gp'] == 0){

                //     $item['registered_gp'] = 'No';

                // }else{

                //     $item['registered_gp'] = 'Yes';
                // }

                $this->metadataProvider->convertDate($item, $component->getName());
                $stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
            }
            $searchCriteria->setCurrentPage(++$i);
            $totalCount = $totalCount - $this->pageSize;
        }
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }
}
