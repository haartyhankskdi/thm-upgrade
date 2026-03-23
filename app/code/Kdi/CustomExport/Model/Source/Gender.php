<?php

namespace Kdi\CustomExport\Model\Source;

 

class Gender implements \Magento\Framework\Option\ArrayInterface

{

   public function toOptionArray()

   {

       $yesNoArray[] = [

           'label' => 'Male',

           'value' => 1,

       ];

       $yesNoArray[] = [

           'label' => 'Female',

           'value' => 2,

       ];

       return $yesNoArray;

   }

}