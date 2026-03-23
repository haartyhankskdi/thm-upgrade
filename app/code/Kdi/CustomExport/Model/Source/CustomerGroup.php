<?php

namespace Kdi\CustomExport\Model\Source;

 

class CustomerGroup implements \Magento\Framework\Option\ArrayInterface

{

   public function toOptionArray()

   {

       $yesNoArray[] = [

           'label' => 'General',

           'value' => 1,

       ];

       $yesNoArray[] = [

           'label' => 'Wholesale',

           'value' => 2,

       ];

       $yesNoArray[] = [

           'label' => 'Retailer',

           'value' => 3,

       ];

       $yesNoArray[] = [

           'label' => 'Suspect',

           'value' => 4,

       ];

       return $yesNoArray;

   }

}